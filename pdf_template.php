<?php
// pdf_template.php
// Template PDF propre et compatible Dompdf
// Génère le HTML (styles intégrés) — image encodée en base64 pour fiabilité

ob_start();
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width" />
  <title>CV — Azza Salmouk</title>

  <style>
    /* -------------------------
       RESET / BASE
       ------------------------- */
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: "DejaVu Sans", sans-serif; }
    html, body { height: 100%; background: #fff; color: #111; -webkit-font-smoothing: antialiased; }
    body { padding: 20px; font-size: 13px; line-height: 1.45; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    /* -------------------------
       HEADER (layout fixe pour PDF)
       ------------------------- */
    .header {
      position: relative;               /* parent pour position absolute de la photo */
      min-height: 160px;
      padding-bottom: 12px;
      margin-bottom: 18px;
      border-bottom: 2px solid #0b84ff;
      display: flex;
      align-items: flex-start;
      gap: 18px;
    }

    .header .left {
      /* occupe l'espace restant à gauche */
      flex: 1 1 auto;
      min-width: 0;
    }

    .left h1 {
      font-size: 22px;
      margin-bottom: 6px;
      font-weight: 700;
    }
    .left .subtitle {
      color: #555;
      font-size: 14px;
      margin-bottom: 10px;
    }

    .contact {
      list-style: none;
      margin-top: 6px;
    }
    .contact li {
      margin-bottom: 4px;
      color: #222;
      font-size: 12.8px;
    }

    /* -------------------------
       PHOTO — un seul élément <img> stylé
       ------------------------- */
    /* Host placé absolument : sert uniquement de conteneur de position */
    .photo-host {
      position: absolute;
      top: 6px;           /* remonte la photo vers le bord supérieur (ajuster si besoin) */
      right: 8px;
      width: 128px;
      height: 128px;
      overflow: visible;
      pointer-events: none; /* pas interactif pour pdf */
    }

    /* L'image elle-même : *seule* bordure et ombre */
    .photo {
      display: block;
      width: 128px;
      height: 128px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #0b84ff;           /* UNIQUE cercle (pas de bord sur parent) */
      box-shadow: 0 3px 10px rgba(0,0,0,0.12);
      background-color: #fff;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    /* -------------------------
       SECTION TITLES & LAYOUT
       ------------------------- */
    h2.section-title {
      font-size: 15.5px;
      color: #0b84ff;
      margin-top: 18px;
      margin-bottom: 8px;
      font-weight: 700;
    }

    p { margin-bottom: 8px; color: #222; }

    /* Timeline / experiences */
    .timeline {
      margin-left: 6px;
      padding-left: 12px;
      border-left: 3px solid #0b84ff;
    }
    .tl-item { margin-bottom: 12px; }
    .tl-date { font-size: 12px; color: #555; margin-bottom: 4px; display:block; }

    /* Project tags */
    .project { margin-bottom: 10px; }
    .tag {
      display: inline-block;
      background: #e7f1ff;
      color: #0b5fd6;
      padding: 2px 6px;
      border-radius: 5px;
      font-size: 11px;
      margin-right: 6px;
    }

    /* Footer simple (si besoin) */
    .pdf-footer {
      margin-top: 20px;
      font-size: 11px;
      color: #666;
      text-align: center;
    }

    /* Ensure no accidental double border from any parent selectors (safety) */
    .photo-host, .photo-host * { border: none !important; box-shadow: none !important; background: transparent !important; padding: 0 !important; margin: 0 !important; }

    /* Print-specific tweaks */
    @media print {
      body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .photo { box-shadow: none; } /* ombre souvent non souhaitée à l'impression */
    }
  </style>
</head>
<body>

  <div class="header" role="banner">
    <div class="left">
      <h1>Azza <strong>Salmouk</strong></h1>
      <div class="subtitle">Élève ingénieure en informatique</div>

      <ul class="contact" aria-label="Coordonnées">
        <li>Email : azzasalmouk20@gmail.com</li>
        <li>Localisation : Sousse, Tunisie</li>
        <li>Téléphone : +216 54 303 136</li>
        <li>LinkedIn : linkedin.com/in/azza-salmouk-7908482a5</li>
      </ul>
    </div>

    <!-- Photo host (position absolute) : NE PAS mettre de bordure ici -->
    <div class="photo-host" aria-hidden="true">
      <?php
        // encode image en base64 — évite problèmes de chemins locaux pour Dompdf
        $imgPath = __DIR__ . '/img/photo.jpg';
        if (file_exists($imgPath)) {
          $photo = base64_encode(file_get_contents($imgPath));
          echo '<img class="photo" src="data:image/jpeg;base64,' . $photo . '" alt="Photo Azza Salmouk" />';
        } else {
          // fallback texte si image absente
          echo '<div style="width:128px;height:128px;border-radius:50%;background:#f2f4f6;border:4px solid #e0e6ef;display:flex;align-items:center;justify-content:center;font-size:11px;color:#888;">Photo<br />manquante</div>';
        }
      ?>
    </div>
  </div>

  <!-- PROFIL -->
  <h2 class="section-title">Profil</h2>
  <p>Élève ingénieure en informatique, passionnée par le développement web, les systèmes embarqués et les technologies modernes. Expérience en développement fullstack, supervision réseau et réalisation de projets techniques variés.</p>

  <!-- COMPETENCES -->
  <h2 class="section-title">Compétences</h2>
  <p><strong>Développement Web :</strong> HTML, CSS, JavaScript, Bootstrap, Node.js, Vue.js, Tailwind CSS</p>
  <p><strong>Mobile :</strong> Flutter</p>
  <p><strong>Langages :</strong> Python, Java, C</p>
  <p><strong>Outils :</strong> VS Code, IntelliJ, Android Studio, Figma, Cordova, Laravel</p>
  <p><strong>Bases de données :</strong> SQL, Oracle, MongoDB</p>

  <!-- EXPERIENCES -->
  <h2 class="section-title">Expériences</h2>
  <div class="timeline" role="list">
    <div class="tl-item" role="listitem">
      <span class="tl-date">07/2025 – 08/2025</span>
      <p><strong>Stage d’été — SRTM</strong></p>
      <p>Refonte du site officiel + intégration GTFS.</p>
    </div>

    <div class="tl-item" role="listitem">
      <span class="tl-date">02/2024 – 05/2024</span>
      <p><strong>Stage PFE — Chaaben Technology Group</strong></p>
      <p>Application IoT d’irrigation automatisée.</p>
    </div>

    <div class="tl-item" role="listitem">
      <span class="tl-date">08/2023</span>
      <p><strong>Stage d’été — UNIMED</strong></p>
      <p>Armoire électrique + suivi production.</p>
    </div>

    <div class="tl-item" role="listitem">
      <span class="tl-date">07/2022</span>
      <p><strong>Saisonnier — Poste Tunisienne</strong></p>
      <p>Saisie et traitement du courrier.</p>
    </div>
  </div>

  <!-- FORMATIONS -->
  <h2 class="section-title">Formations</h2>
  <p><strong>Cycle d'ingénieur Informatique</strong> — 2024–…</p>
  <p><strong>Licence Systèmes Embarqués</strong> — 2021–2024</p>
  <p><strong>Baccalauréat Mathématiques</strong> — 2020–2021</p>

  <!-- PROJETS -->
  <h2 class="section-title">Projets</h2>
  <div class="project">
    <strong>Refonte du site SRTM</strong><br>
    <span class="tag">Vue.js</span>
    <span class="tag">Laravel</span>
    <span class="tag">Tailwind</span>
  </div>

  <div class="project">
    <strong>VortexBid — App d’enchères</strong><br>
    <span class="tag">Flutter</span>
    <span class="tag">Figma</span>
  </div>

  <div class="project">
    <strong>Gestion location de voitures</strong><br>
    <span class="tag">Java</span>
    <span class="tag">MySQL</span>
  </div>

  <div class="project">
    <strong>Reconnaissance faciale embarquée</strong><br>
    <span class="tag">Raspberry Pi</span>
    <span class="tag">OpenCV</span>
    <span class="tag">Python</span>
  </div>

  <div class="pdf-footer">
    &copy; Azza Salmouk — Dernière mise à jour : <?= date('d M. Y') ?>
  </div>

</body>
</html>

<?php
echo ob_get_clean();
?>
