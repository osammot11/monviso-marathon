<nav class="phm-navbar" aria-label="Navigazione principale">
    <a href="/" class="phm-brand"><img src="/images/pescara/logo.png" alt="Pescara Half Marathon 2027"></a>
    <button class="phm-menu-toggle" type="button" aria-expanded="false" aria-controls="phm-menu" onclick="togglePhmMenu(this)">☰</button>
    <div id="phm-menu" class="phm-menu">
        <a href="/#percorso">Percorso</a><a href="/#quote">Quote</a><a href="/#sponsor">Sponsor</a><a href="/regolamento">Regolamento</a><a href="/contattaci">Contatti</a><a href="/iscrizione" class="phm-menu__cta">Iscriviti</a>
    </div>
</nav>
<script>function togglePhmMenu(button){const menu=document.getElementById('phm-menu');const isOpen=menu.classList.toggle('is-open');button.setAttribute('aria-expanded',String(isOpen));button.textContent=isOpen?'×':'☰';}</script>
