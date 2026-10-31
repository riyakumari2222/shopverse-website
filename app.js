document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('.menu-toggle');
  const nav = document.querySelector('.main-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', String(open));
    });
  }
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', event => {
      const button = event.submitter;
      if (button && button.dataset.confirm && !window.confirm(button.dataset.confirm)) {
        event.preventDefault();
      }
    });
  });
});
