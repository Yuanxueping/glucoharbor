<?php $site_name = setting('site_name') ?: 'GlucoHarbor'; $cats = nav_categories(); $fpages = footer_pages(); ?>
</main>
<?php if (setting('code_body_open')): ?><?= setting('code_body_open') ?><?php endif ?>
<footer class="site-footer">
<div class="container">
  <div class="footer-grid">
    <div class="footer-about">
      <h3><i class="fas fa-heartbeat" style="color:#ef4444"></i> <?= e($site_name) ?></h3>
      <p><?= e(setting('site_description')) ?></p>
      <div class="social-links">
        <?php if (setting('social_facebook')): ?><a href="<?= e(setting('social_facebook')) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a><?php endif ?>
        <?php if (setting('social_twitter')): ?><a href="<?= e(setting('social_twitter')) ?>" target="_blank" rel="noopener"><i class="fab fa-x-twitter"></i></a><?php endif ?>
        <?php if (setting('social_instagram')): ?><a href="<?= e(setting('social_instagram')) ?>" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a><?php endif ?>
      </div>
    </div>
    <div class="footer-col">
      <h4>Categories</h4>
      <ul>
        <?php foreach (array_slice($cats,0,6) as $c): ?>
        <li><a href="/category/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a></li>
        <?php endforeach ?>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Legal</h4>
      <ul>
        <?php foreach ($fpages as $p): ?>
        <li><a href="/page/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></li>
        <?php endforeach ?>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Explore</h4>
      <ul>
        <li><a href="/articles">All Articles</a></li>
        <li><a href="/products">Products</a></li>
        <li><a href="/search">Search</a></li>
        <li><a href="/sitemap.xml">Sitemap</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="disclaimer">
      <i class="fas fa-circle-info"></i>
      <small><?= e(setting('medical_disclaimer')) ?></small>
    </div>
    <p class="copyright">
      <?= e(setting('footer_text') ?: '© '.date('Y').' GlucoHarbor. All rights reserved.') ?>
      <?php if (setting('icp_number')): ?> &nbsp;|&nbsp; <?= e(setting('icp_number')) ?><?php endif ?>
    </p>
  </div>
</div>
</footer>
<script src="/assets/js/main.js"></script>
<?php if (setting('code_body_close')): ?><?= setting('code_body_close') ?><?php endif ?>
</body>
</html>
