// Mostrar/Ocultar contraseña con animación y feedback visual
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('togglePassword');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const input = document.getElementById('contrasena');
            const icon = document.getElementById('icon-eye');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
                input.classList.add('input-show');
            } else {
                input.type = "password";
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
                input.classList.remove('input-show');
            }
        });
    }
});

const style = document.createElement('style');
style.innerHTML = `
.input-show {
    animation: showPass 0.2s;
}
@keyframes showPass {
    0% { background: #fffde7; }
    100% { background: #fff; }
}
`;
document.head.appendChild(style);   