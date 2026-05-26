document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.alert').forEach((alert) => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });

    highlightActiveMenu();

    document.querySelectorAll('.btn-delete, .delete-btn').forEach((button) => {
        button.addEventListener('click', function (event) {
            if (!confirm('Ban co chac chan muon xoa?')) {
                event.preventDefault();
            }
        });
    });
});

function highlightActiveMenu() {
    const currentPath = window.location.pathname;
    document.querySelectorAll('.menu-item').forEach((item) => {
        try {
            const itemPath = new URL(item.href, window.location.origin).pathname;
            if (currentPath === itemPath) {
                item.classList.add('active');
            }
        } catch (error) {
            // Ignore malformed href values.
        }
    });
}

function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount || 0);
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';
    toast.style.minWidth = '300px';
    toast.innerHTML = `<i class="fas fa-info-circle"></i><span>${message}</span>`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
