// Progressive enhancement: registration, login and lessons also work without JavaScript.
document.querySelectorAll('[data-password]').forEach((button) => {
  button.addEventListener('click', () => {
    const input = document.getElementById(button.dataset.password);
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    button.textContent = visible ? 'Hide' : 'Show';
    button.setAttribute('aria-pressed', String(visible));
  });
});
const errorSummary = document.querySelector('.form-errors[tabindex]');
if (errorSummary) errorSummary.focus();
