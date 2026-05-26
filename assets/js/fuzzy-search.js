/**
 * Fuzzy Search - Tìm kiếm gợi ý thông minh
 * Tự viết từ đầu bằng Vanilla JavaScript, không sử dụng framework/library
 * 
 * Cách dùng: thêm attribute data-fuzzy-search="employees|products|customers" vào input
 * Script sẽ tự động khởi tạo dropdown gợi ý
 */

(function () {
    'use strict';

    // ====== THUẬT TOÁN FUZZY MATCHING (Levenshtein Distance) ======
    function levenshteinDistance(a, b) {
        a = a.toLowerCase();
        b = b.toLowerCase();
        var matrix = [];
        var i, j;

        for (i = 0; i <= b.length; i++) {
            matrix[i] = [i];
        }
        for (j = 0; j <= a.length; j++) {
            matrix[0][j] = j;
        }

        for (i = 1; i <= b.length; i++) {
            for (j = 1; j <= a.length; j++) {
                if (b.charAt(i - 1) === a.charAt(j - 1)) {
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j - 1] + 1, // thay thế
                        matrix[i][j - 1] + 1,       // chèn
                        matrix[i - 1][j] + 1         // xóa
                    );
                }
            }
        }
        return matrix[b.length][a.length];
    }

    // Tính điểm fuzzy match (0 = không khớp, 1 = khớp hoàn hảo)
    function fuzzyScore(query, text) {
        query = query.toLowerCase().trim();
        text = text.toLowerCase().trim();

        // Khớp chính xác
        if (text === query) return 1;

        // Chứa chuỗi con
        if (text.indexOf(query) !== -1) return 0.9;

        // Bắt đầu bằng chuỗi query
        if (text.indexOf(query) === 0) return 0.95;

        // Levenshtein distance
        var maxLen = Math.max(query.length, text.length);
        if (maxLen === 0) return 1;
        var distance = levenshteinDistance(query, text);
        var score = 1 - (distance / maxLen);

        return score > 0.3 ? score : 0;
    }

    // ====== HIGHLIGHT TỪ KHÓA TRONG KẾT QUẢ ======
    function highlightMatch(text, query) {
        if (!query || !text) return escapeHtml(text || '');
        var escaped = escapeHtml(text);
        var regex = new RegExp('(' + escapeRegex(query) + ')', 'gi');
        return escaped.replace(regex, '<mark>$1</mark>');
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // ====== DEBOUNCE ======
    function debounce(func, delay) {
        var timer;
        return function () {
            var context = this;
            var args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                func.apply(context, args);
            }, delay);
        };
    }

    // ====== LẤY BASE URL CỦA DỰ ÁN ======
    function getBaseUrl() {
        var scripts = document.querySelectorAll('script[src*="fuzzy-search"]');
        if (scripts.length > 0) {
            var src = scripts[0].src;
            // src = .../assets/js/fuzzy-search.js -> baseUrl = .../
            var idx = src.indexOf('/assets/js/fuzzy-search');
            if (idx !== -1) return src.substring(0, idx);
        }
        // Fallback: tìm từ URL hiện tại
        var path = window.location.pathname;
        var match = path.match(/^(\/[^/]+)\//);
        return match ? window.location.origin + match[1] : '';
    }

    // ====== KHỞI TẠO FUZZY SEARCH CHO MỘT INPUT ======
    function initFuzzySearch(input) {
        var searchType = input.getAttribute('data-fuzzy-search');
        if (!searchType) return;

        var baseUrl = getBaseUrl();
        var apiUrl = baseUrl + '/modules/api/fuzzy_search_api.php';

        // Tạo wrapper bao quanh input
        var wrapper = document.createElement('div');
        wrapper.className = 'fuzzy-search-wrapper';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        // Tạo dropdown gợi ý
        var dropdown = document.createElement('div');
        dropdown.className = 'fuzzy-dropdown';
        dropdown.style.display = 'none';
        wrapper.appendChild(dropdown);

        // Tạo badge AI
        var badge = document.createElement('span');
        badge.className = 'fuzzy-ai-badge';
        badge.innerHTML = '<i class="fas fa-magic"></i> AI';
        wrapper.appendChild(badge);

        var activeIndex = -1;
        var currentResults = [];
        var currentXhr = null;

        // ====== GỌI API TÌM KIẾM ======
        function fetchSuggestions(query) {
            if (currentXhr) {
                currentXhr.abort();
            }

            if (query.length < 1) {
                hideDropdown();
                return;
            }

            currentXhr = new XMLHttpRequest();
            currentXhr.open('GET', apiUrl + '?type=' + encodeURIComponent(searchType) + '&q=' + encodeURIComponent(query), true);
            currentXhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            currentXhr.onload = function () {
                if (currentXhr.status === 200) {
                    try {
                        var serverResults = JSON.parse(currentXhr.responseText);

                        // Áp dụng fuzzy scoring phía client để sắp xếp
                        serverResults.forEach(function (item) {
                            var scoreTitle = fuzzyScore(query, item.title);
                            var scoreSub = fuzzyScore(query, item.sub || '');
                            var scoreExtra = fuzzyScore(query, item.extra || '');
                            item._score = Math.max(scoreTitle, scoreSub * 0.8, scoreExtra * 0.6);
                        });

                        // Sắp xếp theo điểm fuzzy giảm dần
                        serverResults.sort(function (a, b) {
                            return b._score - a._score;
                        });

                        currentResults = serverResults;
                        renderDropdown(query, serverResults);
                    } catch (e) {
                        hideDropdown();
                    }
                }
            };

            currentXhr.onerror = function () {
                hideDropdown();
            };

            currentXhr.send();
        }

        // ====== RENDER DROPDOWN ======
        function renderDropdown(query, results) {
            activeIndex = -1;

            if (results.length === 0) {
                dropdown.innerHTML = '<div class="fuzzy-empty">' +
                    '<i class="fas fa-search"></i> Không tìm thấy kết quả cho "' + escapeHtml(query) + '"' +
                    '</div>';
                dropdown.style.display = 'block';
                return;
            }

            var html = '<div class="fuzzy-header">' +
                '<span><i class="fas fa-magic"></i> Gợi ý tìm kiếm</span>' +
                '<span class="fuzzy-count">' + results.length + ' kết quả</span>' +
                '</div>';

            results.forEach(function (item, index) {
                var scorePercent = Math.round((item._score || 0) * 100);
                html += '<div class="fuzzy-item" data-index="' + index + '">' +
                    '<div class="fuzzy-item-icon"><i class="fas fa-' + escapeHtml(item.icon || 'search') + '"></i></div>' +
                    '<div class="fuzzy-item-content">' +
                    '<div class="fuzzy-item-title">' + highlightMatch(item.title, query) + '</div>' +
                    '<div class="fuzzy-item-sub">' +
                    (item.sub ? '<span>' + highlightMatch(item.sub, query) + '</span>' : '') +
                    (item.extra ? '<span class="fuzzy-item-extra">' + escapeHtml(item.extra) + '</span>' : '') +
                    '</div>' +
                    '</div>' +
                    '<div class="fuzzy-item-score" title="Độ khớp: ' + scorePercent + '%">' +
                    '<div class="fuzzy-score-bar"><div class="fuzzy-score-fill" style="width:' + scorePercent + '%"></div></div>' +
                    '</div>' +
                    '</div>';
            });

            dropdown.innerHTML = html;
            dropdown.style.display = 'block';

            // Gắn event click cho từng item
            var items = dropdown.querySelectorAll('.fuzzy-item');
            items.forEach(function (el) {
                el.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    var idx = parseInt(el.getAttribute('data-index'), 10);
                    selectItem(idx);
                });
            });
        }

        function hideDropdown() {
            dropdown.style.display = 'none';
            activeIndex = -1;
            currentResults = [];
        }

        function selectItem(index) {
            if (index >= 0 && index < currentResults.length) {
                input.value = currentResults[index].title;
                hideDropdown();
                // Tự submit form
                var form = input.closest('form');
                if (form) form.submit();
            }
        }

        // ====== ĐIỀU HƯỚNG BẰNG BÀN PHÍM ======
        function updateActiveItem() {
            var items = dropdown.querySelectorAll('.fuzzy-item');
            items.forEach(function (el, i) {
                if (i === activeIndex) {
                    el.classList.add('fuzzy-item-active');
                    el.scrollIntoView({ block: 'nearest' });
                } else {
                    el.classList.remove('fuzzy-item-active');
                }
            });
        }

        // ====== SỰ KIỆN ======
        var debouncedFetch = debounce(fetchSuggestions, 300);

        input.addEventListener('input', function () {
            debouncedFetch(input.value.trim());
        });

        input.addEventListener('focus', function () {
            if (input.value.trim().length >= 1 && currentResults.length > 0) {
                dropdown.style.display = 'block';
            }
        });

        input.addEventListener('blur', function () {
            // Delay để cho phép click vào item
            setTimeout(function () {
                hideDropdown();
            }, 200);
        });

        input.addEventListener('keydown', function (e) {
            var items = dropdown.querySelectorAll('.fuzzy-item');
            var totalItems = items.length;

            if (dropdown.style.display === 'none' || totalItems === 0) return;

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    activeIndex = (activeIndex + 1) % totalItems;
                    updateActiveItem();
                    break;

                case 'ArrowUp':
                    e.preventDefault();
                    activeIndex = activeIndex <= 0 ? totalItems - 1 : activeIndex - 1;
                    updateActiveItem();
                    break;

                case 'Enter':
                    if (activeIndex >= 0) {
                        e.preventDefault();
                        selectItem(activeIndex);
                    }
                    break;

                case 'Escape':
                    hideDropdown();
                    break;
            }
        });

        // Click bên ngoài đóng dropdown
        document.addEventListener('click', function (e) {
            if (!wrapper.contains(e.target)) {
                hideDropdown();
            }
        });
    }

    // ====== TỰ ĐỘNG KHỞI TẠO KHI DOM SẴN SÀNG ======
    function init() {
        var inputs = document.querySelectorAll('[data-fuzzy-search]');
        inputs.forEach(function (input) {
            initFuzzySearch(input);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
