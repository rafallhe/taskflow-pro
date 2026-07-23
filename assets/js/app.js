(() => {
 const body=document.body,theme=document.getElementById('themeToggle'),menu=document.getElementById('menuToggle'),side=document.getElementById('sidebar');
 if(localStorage.getItem('tf-theme')==='light')body.classList.add('light');
 theme?.addEventListener('click',()=>{body.classList.toggle('light');localStorage.setItem('tf-theme',body.classList.contains('light')?'light':'dark')});
 menu?.addEventListener('click',()=>side?.classList.toggle('open'));
 document.querySelectorAll('.alert').forEach(x=>setTimeout(()=>x.style.opacity='.45',4500));

 const langBtn=document.getElementById('langToggle');
 const applyLang=(lang)=>{document.documentElement.lang=lang;document.documentElement.dir=lang==='ar'?'rtl':'ltr';if(langBtn)langBtn.textContent=lang==='ar'?'EN':'AR';};
 let lang=localStorage.getItem('tf-lang')||'en';applyLang(lang);
 langBtn?.addEventListener('click',()=>{lang=lang==='en'?'ar':'en';localStorage.setItem('tf-lang',lang);applyLang(lang);});

 const poll=async()=>{try{const r=await fetch('api/realtime.php',{headers:{'Accept':'application/json'}});if(!r.ok)return;const d=await r.json();document.querySelectorAll('.nav-badge,.bubble').forEach(x=>x.textContent=d.unread);if(d.unread===0)document.querySelectorAll('.nav-badge,.bubble').forEach(x=>x.style.display='none');}catch(e){}};
 if(document.body.classList.contains('auth-page')===false){poll();setInterval(poll,30000);}
 if('serviceWorker' in navigator){navigator.serviceWorker.register('service-worker.js').catch(()=>{});}
})();
