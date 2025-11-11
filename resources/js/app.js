import './bootstrap';
import '../css/app.css';

document.addEventListener('DOMContentLoaded', () => {
  const inputs = document.querySelectorAll('input[type="number"]');
  inputs.forEach((el) => {
    if (!el.hasAttribute('inputmode')) el.setAttribute('inputmode', 'numeric');
    if (!el.hasAttribute('pattern')) el.setAttribute('pattern', '\\d*');
  });
});

document.addEventListener('input', (e) => {
  const t = e.target;
  if (!(t instanceof HTMLInputElement)) return;
  if (!t.matches('input[type="number"]')) return;
  if (t.hasAttribute('data-allow-any')) return;
  const cleaned = t.value.replace(/[^0-9]/g, '');
  if (t.value !== cleaned) t.value = cleaned;
});

document.addEventListener('keydown', (e) => {
  const t = e.target;
  if (!(t instanceof HTMLInputElement)) return;
  if (!t.matches('input[type="number"]')) return;
  if (t.hasAttribute('data-allow-any')) return;
  const allowed = [
    'Backspace','Delete','ArrowLeft','ArrowRight','ArrowUp','ArrowDown',
    'Home','End','Tab'
  ];
  if (allowed.includes(e.key) || (e.ctrlKey || e.metaKey)) return;
  if (["e","E","+","-","."].includes(e.key)) {
    e.preventDefault();
  }
});

document.addEventListener('paste', (e) => {
  const t = e.target;
  if (!(t instanceof HTMLInputElement)) return;
  if (!t.matches('input[type="number"]')) return;
  if (t.hasAttribute('data-allow-any')) return;
  const text = (e.clipboardData || window.clipboardData).getData('text');
  const digits = text.replace(/\D/g, '');
  if (digits !== text) {
    e.preventDefault();
    if (typeof t.setRangeText === 'function' && t.selectionStart !== null) {
      const start = t.selectionStart;
      const end = t.selectionEnd;
      t.setRangeText(digits, start, end, 'end');
    } else {
      document.execCommand('insertText', false, digits);
    }
  }
});
