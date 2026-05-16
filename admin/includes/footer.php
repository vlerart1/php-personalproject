    </main>
    <script>
        // Simple Toast Notification System
        window.showToast = function(message, type = 'success') {
            const container = document.body;
            const toast = document.createElement('div');
            toast.style.position = 'fixed';
            toast.style.bottom = '24px';
            toast.style.right = '24px';
            toast.style.padding = '16px 24px';
            toast.style.borderRadius = '12px';
            toast.style.background = type === 'success' ? '#10b981' : '#ef4444';
            toast.style.color = '#fff';
            toast.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
            toast.style.zIndex = '9999';
            toast.style.display = 'flex';
            toast.style.alignItems = 'center';
            toast.style.gap = '12px';
            toast.style.fontSize = '0.9rem';
            toast.style.fontWeight = '500';
            toast.style.animation = 'slideIn 0.3s ease-out forwards';
            
            toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-in forwards';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        };

        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
            @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
