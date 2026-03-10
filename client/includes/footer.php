<footer class="footer mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-title">Les etoiles du Sine<span style="color:var(--gold)">Immo</span></div>
        <p style="font-size:0.88rem; opacity:0.6; line-height:1.7;">
          Votre partenaire de confiance pour tous vos projets immobiliers. Maisons, terrains, immeubles — nous trouvons le bien idéal pour vous.
        </p>
        <div class="d-flex gap-3 mt-2">
          <a href="#" style="color:rgba(255,255,255,0.5); font-size:1.2rem; transition:color 0.2s;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='rgba(255,255,255,0.5)'"><i class="bi bi-facebook"></i></a>
          <a href="#" style="color:rgba(255,255,255,0.5); font-size:1.2rem; transition:color 0.2s;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='rgba(255,255,255,0.5)'"><i class="bi bi-instagram"></i></a>
          <a href="https://vm.tiktok.com/ZS9d45QsXSwYX-ZbrER/" style="color:rgba(255,255,255,0.5); font-size:1.2rem; transition:color 0.2s;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='rgba(255,255,255,0.5)'"><i class="bi bi-tiktok"></i></a>
          <a href="https://youtube.com/@moustaphafaye607?si=UE_RoGsNCDcAWLEY" style="color:rgba(255,255,255,0.5); font-size:1.2rem; transition:color 0.2s;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='rgba(255,255,255,0.5)'"><i class="bi bi-youtube"></i></a>
          <a href="https://wa.me/qr/K7BH2Y3KR25QL1" style="color:rgba(255,255,255,0.5); font-size:1.2rem; transition:color 0.2s;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='rgba(255,255,255,0.5)'"><i class="bi bi-whatsapp"></i></a>
        </div>
      </div>
      <div class="col-lg-2 col-6">
        <div class="footer-title" style="font-size:0.95rem;">Navigation</div>
        <a href="<?= SITE_URL ?>/index.php" class="footer-link">Accueil</a>
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php" class="footer-link">Tous les biens</a>
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php?transaction=vente" class="footer-link">À Vendre</a>
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php?transaction=location" class="footer-link">À Louer</a>
      </div>
      <div class="col-lg-2 col-6">
        <div class="footer-title" style="font-size:0.95rem;">Types</div>
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php?type=maison" class="footer-link">Maisons</a>
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php?type=immeuble" class="footer-link">Immeubles</a>
        <a href="<?= SITE_URL ?>/client/pages/catalogue.php?type=terrain" class="footer-link">Terrains</a>
      </div>
      <div class="col-lg-4">
        <div class="footer-title" style="font-size:0.95rem;">Contact</div>
        <p class="footer-link"><i class="bi bi-geo-alt me-2" style="color:var(--gold)"></i>Dakar, Sénégal</p>
        <p class="footer-link"><i class="bi bi-telephone me-2" style="color:var(--gold)"></i>
        <a href="tel:+221770000000" style="color:inherit; text-decoration:underline;">+221 77 000 00 00</a>
        </p>
        <p class="footer-link"><i class="bi bi-envelope me-2" style="color:var(--gold)"></i>contact@agence.com</p>
      </div>
    </div>
    <div class="footer-bottom">
      © <?= date('Y') ?> Les etoiles du Sine Immo — Tous droits réservés
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
