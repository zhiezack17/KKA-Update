   <script>
  // Auto-close flash after 5s
  document.querySelectorAll('.flash').forEach(el=>{
    setTimeout(()=>{ el.style.opacity='0'; setTimeout(()=>el.remove(), 400); }, 5000);
  });

  // Format currency input
  document.querySelectorAll('input[data-money]').forEach(inp=>{
    const fmt = v => {
      v = String(v||'').replace(/[^\d]/g,'');
      if(!v) return '';
      return v.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };
    inp.value = fmt(inp.value);
    inp.addEventListener('input', e => { e.target.value = fmt(e.target.value); });
  });

  // Mobile sidebar toggle
  (function(){
    const btn = document.getElementById('mobileMenuBtn');
    const closeBtn = document.getElementById('sidebarCloseBtn');
    const sb  = document.querySelector('.sidebar');
    const bd  = document.getElementById('mobileBackdrop');
    if(!sb || !bd) return;

    function closeMenu(){
      sb.classList.remove('open');
      bd.classList.remove('show');
      document.body.classList.remove('menu-open');
    }
    function openMenu(){
      sb.classList.add('open');
      bd.classList.add('show');
      document.body.classList.add('menu-open');
    }

    if (btn) btn.addEventListener('click', function(e){ e.preventDefault(); openMenu(); });
    if (closeBtn) closeBtn.addEventListener('click', function(e){ e.preventDefault(); closeMenu(); });

    bd.addEventListener('click', closeMenu);
    document.querySelectorAll('.sidebar .nav-item, .sidebar .logout').forEach(el=>{
      el.addEventListener('click', closeMenu);
    });

    window.addEventListener('resize', function(){
      if(window.innerWidth > 992) closeMenu();
    });
  })();
</script>
</body>
</html>