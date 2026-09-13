document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('form[data-validar]').forEach(form => {
    form.addEventListener('submit', e => {
      let ok = true;
      form.querySelectorAll('[required]').forEach(c => {
        c.classList.remove('campo-erro');
        if (!c.value.trim()) { c.classList.add('campo-erro'); ok = false; }
      });
      const email = form.querySelector('input[type=email]');
      if (email && email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) ok = false;
      if (!ok) { e.preventDefault(); alert('Revise os campos obrigatórios do formulário.'); }
    });
  });

  document.querySelectorAll('.faq button').forEach(btn => {
    btn.addEventListener('click', () => {
      const panel = btn.nextElementSibling;
      panel.hidden = !panel.hidden;
    });
  });

  const slides = [...document.querySelectorAll('.slide')];
  if (slides.length) {
    let i = 0; slides[0].classList.add('ativo');
    setInterval(() => { slides[i].classList.remove('ativo'); i=(i+1)%slides.length; slides[i].classList.add('ativo'); }, 3500);
  }

  const select = document.querySelector('#ordenar-servicos'), list = document.querySelector('#lista-servicos');
  if (select && list) {
    select.addEventListener('change', () => {
      const cards = [...list.children];
      cards.sort((a,b) => select.value === 'nome'
        ? a.dataset.nome.localeCompare(b.dataset.nome)
        : Number(a.dataset.preco) - Number(b.dataset.preco));
      cards.forEach(c => list.appendChild(c));
    });
  }

  document.querySelectorAll('form[data-confirmar]').forEach(f => f.addEventListener('submit', e => {
    if (!confirm(f.dataset.confirmar)) e.preventDefault();
  }));
});
