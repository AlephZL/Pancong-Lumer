/* =====================================================
   js/validasi.js — Vanilla JS (Stitch-adapted)
   - Modal customizer + rekomendasi topping
   - Keranjang (localStorage) + badge + mini-cart + FAB
   - Sinkron search header + tipe takeaway/dine_in
   - Validasi form checkout & admin, filter katalog
   ===================================================== */
(function () {
  'use strict';

  /* ---------- helpers ---------- */
  const $ = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));
  const rp = (n) => 'Rp' + Number(n || 0).toLocaleString('id-ID');

  function getCart() {
    try { return JSON.parse(localStorage.getItem('pl_cart') || '[]'); }
    catch (e) { return []; }
  }
  function saveCart(c) {
    localStorage.setItem('pl_cart', JSON.stringify(c));
    updateBadge();
  }
  function cartTotal(c) {
    return (c || []).reduce((a, it) => a + (parseInt(it.subtotal, 10) || 0), 0);
  }
  function cartCount(c) {
    return (c || []).reduce((a, it) => a + (parseInt(it.qty, 10) || 0), 0);
  }

  /* ---------- tipe takeaway / dine_in (persist) ---------- */
  function getTipe() {
    return localStorage.getItem('pl_tipe') === 'dine_in' ? 'dine_in' : 'takeaway';
  }
  window.setTipe = function (tipe) {
    const v = (tipe === 'dine_in') ? 'dine_in' : 'takeaway';
    try { localStorage.setItem('pl_tipe', v); } catch (e) {}
    const tw = $('#tipeTakeaway'), di = $('#tipeDineIn');
    if (tw) tw.classList.toggle('on', v === 'takeaway');
    if (di) di.classList.toggle('on', v === 'dine_in');
    $$('input[name="tipe_order"]').forEach((r) => {
      r.checked = (r.value === v);
    });
  };

  /* ---------- badge + header total + FAB + mini-cart ---------- */
  function updateBadge() {
    const c = getCart();
    const n = cartCount(c);
    const tot = cartTotal(c);
    const b = $('#cartCount');
    if (b) {
      b.textContent = n;
      b.style.display = n > 0 ? 'flex' : 'none';
    }
    const head = $('#cartTotalHead');
    if (head) head.textContent = rp(tot);
    const dot = $('#navDot');
    if (dot) dot.style.display = n > 0 ? 'block' : 'none';
    const fab = $('#fabBar');
    if (fab) {
      // FAB hanya untuk mobile; di desktop mini-cart aside sudah terlihat
      fab.classList.toggle('show', n > 0 && window.innerWidth < 768);
    }
    const fc = $('#fabCount'), ft = $('#fabTotal');
    if (fc) fc.textContent = n + ' item';
    if (ft) ft.textContent = rp(tot);
    renderMiniCart(c);
  }
  // expose untuk debugging
  window.updateBadge = updateBadge;

  function labelMatang(k) {
    return k === 'setengah_matang' ? '1/2 Matang Lumer'
      : k === 'tiga_perempat' ? '3/4 Matang' : 'Matang Sempurna';
  }
  function labelPorsi(it) {
    if ((it.nama || '').toLowerCase().indexOf('roll') !== -1) return '';
    return it.porsi === 'half_loyang' ? '1/2 Loyang' : '1 Loyang';
  }
  function monogramFor(nama) {
    const kata = String(nama || '').trim().split(/\s+/).slice(0, 2);
    const m = kata.map((k) => (k.charAt(0) || '').toUpperCase()).join('');
    return m || 'PL';
  }

  /* ---------- mini-cart aside (beranda) ---------- */
  window.renderMiniCart = function (c) {
    const box = $('#miniItems');
    if (!box) return;
    c = c || getCart();
    const cnt = $('#miniCount'), sum = $('#miniSum');
    const sub = $('#miniSub'), tot = $('#miniTotal'), cta = $('#miniCta');
    if (!c.length) {
      box.innerHTML = '<div class="mini-empty"><p><b>Keranjang masih kosong.</b><br>Pilih menu favoritmu dulu.</p></div>';
      if (cnt) cnt.textContent = '0 item';
      if (sum) sum.style.display = 'none';
      if (cta) cta.classList.add('disabled');
      return;
    }
    if (cnt) cnt.textContent = cartCount(c) + ' item';
    if (sum) sum.style.display = '';
    const total = cartTotal(c);
    if (sub) sub.textContent = rp(total);
    if (tot) tot.textContent = rp(total);
    if (cta) cta.classList.remove('disabled');
    box.innerHTML = c.map((it, i) => {
      const tops = (it.toppings || []).map((t) => t.nama).join(', ');
      const ket = [labelPorsi(it), it.kematangan ? labelMatang(it.kematangan) : '']
        .filter(Boolean).join(' • ');
      return '<div>' +
        '<div class="mini-row"><div><h4>' + escapeHtml(it.nama) + '</h4>' +
        (ket ? '<p>' + escapeHtml(ket) + '</p>' : '') +
        (tops ? '<p>Topping: ' + escapeHtml(tops) + '</p>' : '') +
        '<button type="button" class="mini-link" onclick="hapusItem(' + i + ')">Hapus</button></div>' +
        '<div class="mini-price">' + rp(it.subtotal) + '</div></div>' +
        '<div class="mini-row" style="margin-top:8px"><div class="stepper-qty">' +
        '<button type="button" onclick="ubahQtyMini(' + i + ',-1)" aria-label="Kurangi">−</button>' +
        '<b>' + (parseInt(it.qty, 10) || 1) + '</b>' +
        '<button type="button" onclick="ubahQtyMini(' + i + ',1)" aria-label="Tambah">+</button>' +
        '</div></div></div>';
    }).join('');
  };
  function renderMiniCart(c) { window.renderMiniCart(c); }

  window.ubahQtyMini = function (i, d) {
    const c = getCart();
    if (!c[i]) return;
    const qty = Math.min(20, Math.max(1, (parseInt(c[i].qty, 10) || 1) + d));
    const per = Math.round((parseInt(c[i].subtotal, 10) || 0) / Math.max(1, parseInt(c[i].qty, 10) || 1));
    c[i].qty = qty;
    c[i].subtotal = per * qty;
    saveCart(c);
    renderCheckoutTable();
  };

  function escapeHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, (m) =>
      ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
  }

  /* ---------- search sinkron (desktop + mobile + hidden #q) ---------- */
  window.syncSearch = function (val, src) {
    $$('.js-search').forEach((el) => { if (el !== src) el.value = val; });
    const hidden = $('#q');
    if (hidden && hidden !== src) hidden.value = val;
  };

  /* ---------- filter katalog ---------- */
  window.filterKategori = function (kode, btn) {
    $$('.chip[data-kat]').forEach((c) => c.classList.remove('active'));
    if (btn) btn.classList.add('active');
    $$('[data-catcode]').forEach((card) => {
      const show = (kode === 'semua' || card.getAttribute('data-catcode') === kode);
      card.style.display = show ? '' : 'none';
    });
    const q = $('#q');
    const qd = $('#qDesktop');
    const kw = (q && q.value) || (qd && qd.value) || '';
    if (kw) window.cariMenu(kw);
    else updateMenuCount();
  };
  window.cariMenu = function (kw) {
    kw = (kw || '').toLowerCase();
    const hidden = $('#q');
    if (hidden && document.activeElement !== hidden) hidden.value = kw;
    $$('[data-catcode]').forEach((card) => {
      const nama = (card.getAttribute('data-nama') || '').toLowerCase();
      const aktif = $('.chip[data-kat].active');
      const kat = aktif ? aktif.getAttribute('data-kat') : 'semua';
      const okKat = (kat === 'semua' || card.getAttribute('data-catcode') === kat);
      card.style.display = (okKat && nama.indexOf(kw) !== -1) ? '' : 'none';
    });
    updateMenuCount();
  };
  function updateMenuCount() {
    const el = $('#menuCount');
    if (!el) return;
    const vis = $$('[data-catcode]').filter((c) => c.style.display !== 'none').length;
    const tot = $$('[data-catcode]').length;
    el.textContent = (vis === tot) ? (tot + ' menu') : (vis + ' dari ' + tot + ' menu');
  }

  /* ---------- modal customizer ---------- */
  let CUR = null; // {product, qty}

  window.openCustomizer = function (pid) {
    const products = window.PL_PRODUCTS || [];
    const p = products.find((x) => String(x.id) === String(pid));
    if (!p) { notif('Menu Tidak Ditemukan', 'Data menu tidak tersedia. Coba refresh halaman katalog.', 'error'); return; }
    CUR = { product: p, qty: 1 };
    $('#mNama').textContent = p.nama_menu;
    $('#mDesk').textContent = p.deskripsi || '';
    $('#mHarga').textContent = rp(p.harga_dasar);
    $('#mPid').value = p.id;
    const em = $('#mEmoji');
    if (em) em.textContent = monogramFor(p.nama_menu);

    const isRoll = (p.kode_kategori === 'chicken_roll');
    $('#wrapPorsi').style.display = isRoll ? 'none' : '';
    $('#wrapMatang').style.display = isRoll ? 'none' : '';
    $$('input[name="porsi"]').forEach((r) => {
      r.checked = (r.value === '1_loyang');
    });
    $$('input[name="kematangan"]').forEach((r) => {
      r.checked = (r.value === 'setengah_matang');
    });

    // topping list
    const tops = window.PL_TOPPINGS || [];
    const box = $('#topList');
    box.innerHTML = '';
    tops.forEach((t) => {
      if (String(t.is_available) !== '1') return;
      const label = document.createElement('label');
      label.className = 'top-item';
      label.innerHTML =
        '<input type="checkbox" name="topping" value="' + t.id + '" data-nama="' +
        String(t.nama_topping).replace(/"/g, '&quot;') + '" data-harga="' + t.harga + '">' +
        '<span>' + escapeHtml(t.nama_topping) + '</span><b>+' + rp(t.harga) + '</b>';
      label.querySelector('input').addEventListener('change', onToppingChange);
      box.appendChild(label);
    });

    $$('input[name="porsi"]').forEach((r) => {
      if (!r._plBound) { r.addEventListener('change', updateTotal); r._plBound = true; }
    });
    $$('input[name="kematangan"]').forEach((r) => {
      if (!r._plBound) { r.addEventListener('change', updateTotal); r._plBound = true; }
    });
    $('#mQty').textContent = '1';
    $('#rekomBox').classList.remove('show');
    $('#rekomList').innerHTML = '';
    updateTotal();
    $('#modalCustom').classList.add('open');
    document.body.style.overflow = 'hidden';
  };

  window.closeCustomizer = function () {
    const m = $('#modalCustom');
    if (m) m.classList.remove('open');
    document.body.style.overflow = '';
  };

  window.chQty = function (d) {
    if (!CUR) return;
    CUR.qty = Math.min(20, Math.max(1, (CUR.qty || 1) + d));
    $('#mQty').textContent = CUR.qty;
    updateTotal();
  };

  function basePrice() {
    if (!CUR) return 0;
    const hd = parseInt(CUR.product.harga_dasar, 10) || 0;
    if (CUR.product.kode_kategori === 'chicken_roll') return hd;
    const porsi = ($('input[name="porsi"]:checked') || {}).value || '1_loyang';
    if (porsi === 'half_loyang') return Math.round(hd / 2);
    return hd;
  }

  function selectedToppings() {
    return $$('#topList input[name="topping"]:checked').map((c) => ({
      id: c.value,
      nama: c.getAttribute('data-nama'),
      harga: parseInt(c.getAttribute('data-harga'), 10) || 0
    }));
  }

  function onToppingChange() {
    updateTotal();
    renderRekom();
  }

  function renderRekom() {
    const box = $('#rekomBox'), list = $('#rekomList');
    if (!box || !list) return;
    const chosen = selectedToppings().map((t) => String(t.id));
    const R = window.PL_REKOM || {}; // {base_id: [{id,nama,harga,alasan}]}
    const saran = [];
    chosen.forEach((bid) => {
      (R[bid] || R[String(bid)] || []).forEach((r) => {
        if (chosen.indexOf(String(r.id)) === -1 && !saran.find((s) => String(s.id) === String(r.id))) {
          saran.push(r);
        }
      });
    });
    if (!saran.length) { box.classList.remove('show'); list.innerHTML = ''; return; }
    box.classList.add('show');
    list.innerHTML = '';
    saran.slice(0, 3).forEach((r) => {
      const b = document.createElement('button');
      b.type = 'button';
      b.textContent = '+ ' + r.nama + ' (' + rp(r.harga) + ')';
      b.title = r.alasan || '';
      b.onclick = function () {
        const cb = $('#topList input[name="topping"][value="' + r.id + '"]');
        if (cb) { cb.checked = true; onToppingChange(); }
      };
      list.appendChild(b);
    });
    const why = saran[0] && saran[0].alasan
      ? saran[0].alasan
      : 'Kombinasi terfavorit pelanggan.';
    $('#rekomWhy').textContent = 'Pasangan topping terfavorit: ' + why;
  }

  function updateTotal() {
    if (!CUR) return;
    const tops = selectedToppings();
    const per = basePrice() + tops.reduce((a, t) => a + t.harga, 0);
    const tot = per * (CUR.qty || 1);
    $('#mTotal').textContent = rp(tot);
    $('#mSatuan').textContent = rp(per) + ' / porsi';
  }

  window.tambahKeranjang = function () {
    if (!CUR) return;
    const isRoll = (CUR.product.kode_kategori === 'chicken_roll');
    const porsi = isRoll ? '1_loyang'
      : (($('input[name="porsi"]:checked') || {}).value || '1_loyang');
    const matang = isRoll ? null
      : (($('input[name="kematangan"]:checked') || {}).value || 'matang');
    const tops = selectedToppings();
    const base = basePrice();
    const per = base + tops.reduce((a, t) => a + t.harga, 0);
    const cart = getCart();
    cart.push({
      product_id: CUR.product.id,
      nama: CUR.product.nama_menu,
      harga_dasar: base,
      porsi: porsi,
      kematangan: matang,
      qty: CUR.qty || 1,
      toppings: tops,
      subtotal: per * (CUR.qty || 1)
    });
    saveCart(cart);
    closeCustomizer();
    renderCheckoutTable();
    toast('Ditambahkan ke keranjang', 'success');
  };

  /* =====================================================
     SISTEM NOTIFIKASI TENGAH (pengganti alert()/toast polos)
     - toast(msg, type)  : kartu tengah non-blocking, hilang otomatis
                           type: success|error|warning|info (default success)
                           Contoh: toast('Ditambahkan ke keranjang', 'success')
     - notif(title, msg, type, btnText) : dialog tengah blocking dengan
                           backdrop blur + tombol OK. Mengembalikan Promise
                           yang resolve saat ditutup.
                           Contoh: await notif('Gagal', 'Stok habis.', 'error')
     - confirmBox(title, msg, okText, cancelText) : dialog tengah Ya/Batal.
                           Mengembalikan Promise<boolean> (true = disetujui).
                           Contoh: if (await confirmBox('Hapus?', '...')) hapus()
     Catatan: semua teks dimasukkan via textContent (anti-XSS).
     ===================================================== */
  const NOTIF_ICON = {
    success: { emoji: '✓', label: 'Berhasil' },
    error: { emoji: '×', label: 'Gagal' },
    warning: { emoji: '!', label: 'Perhatian' },
    info: { emoji: 'i', label: 'Info' }
  };
  function normType(t) {
    return NOTIF_ICON[t] ? t : 'info';
  }

  /* ---------- toast kartu tengah (non-blocking) ---------- */
  function toast(msg, type) {
    type = normType(type || 'success');
    let t = $('#plToast');
    if (!t) {
      t = document.createElement('div');
      t.id = 'plToast';
      t.setAttribute('role', 'status');
      t.innerHTML = '<span class="t-ic"></span><span class="t-msg"></span><span class="t-bar"><i></i></span>';
      document.body.appendChild(t);
    }
    t.querySelector('.t-ic').className = 't-ic ' + type;
    t.querySelector('.t-ic').textContent = NOTIF_ICON[type].emoji;
    t.querySelector('.t-msg').textContent = msg;
    const bar = t.querySelector('.t-bar i');
    if (bar) { bar.style.animation = 'none'; void bar.offsetWidth; bar.style.animation = ''; }
    t.classList.add('show');
    clearTimeout(t._h);
    t._h = setTimeout(() => t.classList.remove('show'), 2200);
  }
  window.plToast = toast;
  window.toast = toast;

  /* ---------- dialog tengah blocking ---------- */
  function ensureNotifBackdrop() {
    let bd = $('#plNotifBackdrop');
    if (bd) return bd;
    bd = document.createElement('div');
    bd.id = 'plNotifBackdrop';
    bd.innerHTML =
      '<div class="pl-notif-card" role="alertdialog" aria-modal="true" aria-labelledby="plNotifTitle">' +
      '<button type="button" class="pl-notif-x" aria-label="Tutup">✕</button>' +
      '<div class="pl-notif-icon"></div>' +
      '<h3 class="pl-notif-title" id="plNotifTitle"></h3>' +
      '<p class="pl-notif-msg"></p>' +
      '<button type="button" class="pl-notif-btn">Mengerti</button>' +
      '</div>';
    document.body.appendChild(bd);
    return bd;
  }

  function notif(title, msg, type, btnText) {
    type = normType(type || 'info');
    const bd = ensureNotifBackdrop();
    const icon = bd.querySelector('.pl-notif-icon');
    icon.className = 'pl-notif-icon ' + type;
    icon.textContent = NOTIF_ICON[type].emoji;
    bd.querySelector('.pl-notif-title').textContent = title || NOTIF_ICON[type].label;
    bd.querySelector('.pl-notif-msg').textContent = msg || '';
    const btn = bd.querySelector('.pl-notif-btn');
    btn.className = 'pl-notif-btn ' + type;
    btn.textContent = btnText || 'Mengerti';
    bd.classList.add('show');
    document.body.style.overflow = 'hidden';
    return new Promise((resolve) => {
      const done = () => {
        bd.classList.remove('show');
        if (!$('#modalCustom') || !$('#modalCustom').classList.contains('open')) {
          document.body.style.overflow = '';
        }
        btn.removeEventListener('click', done);
        x.removeEventListener('click', done);
        bd.removeEventListener('click', onBg);
        document.removeEventListener('keydown', onKey);
        resolve();
      };
      const onBg = (e) => { if (e.target === bd) done(); };
      const onKey = (e) => { if (e.key === 'Escape' || e.key === 'Enter') done(); };
      const x = bd.querySelector('.pl-notif-x');
      btn.addEventListener('click', done);
      x.addEventListener('click', done);
      bd.addEventListener('click', onBg);
      document.addEventListener('keydown', onKey);
      setTimeout(() => btn.focus(), 50);
    });
  }
  window.notif = notif;

  /* ---------- dialog konfirmasi tengah (pengganti confirm()) ---------- */
  function confirmBox(title, msg, okText, cancelText) {
    const bd = ensureNotifBackdrop();
    const icon = bd.querySelector('.pl-notif-icon');
    icon.className = 'pl-notif-icon warning';
    icon.textContent = NOTIF_ICON.warning.emoji;
    bd.querySelector('.pl-notif-title').textContent = title || 'Yakin?';
    bd.querySelector('.pl-notif-msg').textContent = msg || '';
    const btn = bd.querySelector('.pl-notif-btn');
    btn.className = 'pl-notif-btn warning';
    btn.textContent = okText || 'Ya, Lanjutkan';
    let cancelBtn = bd.querySelector('.pl-notif-cancel');
    if (!cancelBtn) {
      cancelBtn = document.createElement('button');
      cancelBtn.type = 'button';
      cancelBtn.className = 'pl-notif-btn pl-notif-cancel';
      cancelBtn.style.cssText = 'background:var(--low);color:var(--cocoa);box-shadow:none;margin-top:8px';
      btn.after(cancelBtn);
    }
    cancelBtn.style.display = '';
    cancelBtn.textContent = cancelText || 'Batal';
    bd.classList.add('show');
    document.body.style.overflow = 'hidden';
    return new Promise((resolve) => {
      const done = (val) => {
        bd.classList.remove('show');
        if (!$('#modalCustom') || !$('#modalCustom').classList.contains('open')) {
          document.body.style.overflow = '';
        }
        btn.removeEventListener('click', onOk);
        cancelBtn.removeEventListener('click', onNo);
        x.removeEventListener('click', onNo);
        bd.removeEventListener('click', onBg);
        document.removeEventListener('keydown', onKey);
        cancelBtn.style.display = 'none';
        resolve(val);
      };
      const onOk = () => done(true);
      const onNo = () => done(false);
      const onBg = (e) => { if (e.target === bd) done(false); };
      const onKey = (e) => { if (e.key === 'Escape') done(false); };
      const x = bd.querySelector('.pl-notif-x');
      btn.addEventListener('click', onOk);
      cancelBtn.addEventListener('click', onNo);
      x.addEventListener('click', onNo);
      bd.addEventListener('click', onBg);
      document.addEventListener('keydown', onKey);
      setTimeout(() => cancelBtn.focus(), 50);
    });
  }
  window.confirmBox = confirmBox;

  /* ---------- checkout ---------- */
  window.hapusItem = function (i) {
    const c = getCart();
    if (!c[i]) return;
    const nama = c[i].nama || 'menu ini';
    confirmBox('Hapus dari Keranjang?', '"' + nama + '" akan dihapus dari keranjang.', 'Ya, Hapus', 'Batal')
      .then((ya) => {
        if (!ya) return;
        const cc = getCart();
        cc.splice(i, 1);
        saveCart(cc);
        renderCheckoutTable();
        toast('Item dihapus', 'info');
      });
  };

  window.renderCheckoutTable = function () {
    const tb = $('#cartBody');
    // selalu sinkronkan badge/mini-cart walau bukan di halaman checkout
    updateBadgeSilent();
    if (!tb) return;
    const c = getCart();
    if (!c.length) {
      tb.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#5b403c;padding:24px">' +
        'Keranjang masih kosong. <a href="index.php">Kembali ke katalog</a></td></tr>';
    } else {
      tb.innerHTML = c.map((it, i) => {
        const top = (it.toppings || []).map((t) => escapeHtml(t.nama)).join(', ') || '-';
        const ket = (it.porsi === 'half_loyang' ? '1/2 Loyang' : ((it.nama || '').toLowerCase().indexOf('roll') !== -1 ? '' : '1 Loyang')) +
          (it.kematangan ? ' • ' + labelMatang(it.kematangan) : '') +
          '<br><small style="color:#5b403c">Topping: ' + top + '</small>';
        return '<tr><td><b>' + escapeHtml(it.nama) + '</b><br><small style="color:#5b403c">' + ket +
          '</small></td><td style="text-align:center">' + (parseInt(it.qty, 10) || 1) + 'x</td>' +
          '<td style="text-align:right;font-variant-numeric:tabular-nums"><b>' + rp(it.subtotal) + '</b></td>' +
          '<td style="text-align:center"><button type="button" class="btn btn-ghost btn-sm" onclick="hapusItem(' + i + ')">Hapus</button></td></tr>';
      }).join('');
    }
    const total = cartTotal(c);
    const el = $('#grandTotal');
    if (el) el.textContent = rp(total);
    const mc = $('#miniSubCo');
    if (mc) mc.textContent = rp(total);
    const hj = $('#cartJson');
    if (hj) hj.value = JSON.stringify(c);
    const kosong = $('#btnBayar');
    if (kosong) kosong.disabled = c.length === 0;
  };

  // update badge tanpa memicu renderCheckoutTable (hindari loop)
  function updateBadgeSilent() {
    const c = getCart();
    const n = cartCount(c);
    const tot = cartTotal(c);
    const b = $('#cartCount');
    if (b) { b.textContent = n; b.style.display = n > 0 ? 'flex' : 'none'; }
    const head = $('#cartTotalHead');
    if (head) head.textContent = rp(tot);
    const dot = $('#navDot');
    if (dot) dot.style.display = n > 0 ? 'block' : 'none';
    const fab = $('#fabBar');
    if (fab) {
      fab.classList.toggle('show', n > 0 && window.innerWidth < 768);
      const fc = $('#fabCount'), ft = $('#fabTotal');
      if (fc) fc.textContent = n + ' item';
      if (ft) ft.textContent = rp(tot);
    }
    window.renderMiniCart(c);
  }

  window.togglePay = function () {
    const v = ($('input[name="metode_bayar"]:checked') || {}).value || 'qris';
    const q = $('#qrisArea'), f = $('#buktiWrap');
    if (q) q.style.display = v === 'qris' ? '' : 'none';
    if (f) f.style.display = v === 'qris' ? '' : 'none';
  };

  window.validasiCheckout = function () {
    let ok = true;
    const need = [
      ['nama_pemesan', (v) => v.trim().length >= 3, 'Nama minimal 3 huruf.'],
      ['no_wa', (v) => /^08[0-9]{7,13}$/.test(v.trim()), 'No. WA harus diawali 08, 9–15 digit.']
    ];
    need.forEach(([name, fn, msg]) => {
      const inp = $('[name="' + name + '"]');
      const err = $('#err_' + name);
      if (!inp) return;
      const valid = fn(inp.value || '');
      inp.classList.toggle('invalid', !valid);
      if (err) {
        err.textContent = valid ? '' : msg;
        err.classList.toggle('show', !valid);
      }
      if (!valid) ok = false;
    });
    const c = getCart();
    if (!c.length) { notif('Keranjang Kosong', 'Pilih minimal 1 menu di katalog dulu sebelum checkout.', 'warning'); return false; }
    const pay = ($('input[name="metode_bayar"]:checked') || {}).value || 'qris';
    if (pay === 'qris') {
      const f = $('[name="bukti_bayar"]');
      if (f && (!f.files || !f.files.length)) {
        const err = $('#err_bukti_bayar');
        if (err) { err.textContent = 'Wajib unggah bukti transfer QRIS (JPG/PNG).'; err.classList.add('show'); }
        f.classList.add('invalid');
        notif('Bukti Bayar Belum Ada', 'Unggah screenshot bukti transfer QRIS (JPG/PNG, maks 2MB) dulu ya.', 'warning');
        return false;
      }
      if (f && f.files && f.files.length) {
        const fl = f.files[0];
        const allow = ['image/jpeg', 'image/png', 'image/jpg'];
        if (allow.indexOf(fl.type) === -1 || fl.size > 2 * 1024 * 1024) {
          notif('File Tidak Valid', 'Bukti harus JPG/PNG dengan ukuran maks 2MB.', 'error');
          return false;
        }
      }
    }
    const hj = $('#cartJson');
    if (hj) hj.value = JSON.stringify(c);
    if (!ok) notif('Form Belum Lengkap', 'Periksa kembali kolom bertanda merah pada form.', 'warning');
    return ok;
  };

  /* ---------- validasi admin generik ---------- */
  window.validasiAdmin = function (form) {
    let ok = true;
    $$('[required]', form).forEach((inp) => {
      const bad = !(inp.value || '').toString().trim();
      inp.classList.toggle('invalid', !!bad);
      if (bad) ok = false;
    });
    $$('input[type="number"][min]', form).forEach((inp) => {
      if (inp.value !== '' && parseFloat(inp.value) < parseFloat(inp.getAttribute('min'))) {
        inp.classList.add('invalid'); ok = false;
      }
    });
    if (!ok) notif('Form Belum Lengkap', 'Lengkapi kolom bertanda * (merah) sebelum menyimpan.', 'warning');
    return ok;
  };

  /* ---------- auto-popup tengah untuk flash message server ---------- */
  function popupFlashServer() {
    const params = new URLSearchParams(window.location.search);
    const msg = params.get('msg');
    const err = params.get('err');
    if (err) {
      notif('Terjadi Kesalahan', err, 'error');
    } else if (msg) {
      toast(msg, 'success');
    }
    // tandai alert server agar bisa ditutup manual (tombol ×)
    $$('.alert').forEach((a) => {
      if (a.querySelector('.alert-dismiss')) return;
      const x = document.createElement('button');
      x.type = 'button';
      x.className = 'alert-dismiss';
      x.setAttribute('aria-label', 'Tutup');
      x.textContent = '✕';
      x.onclick = () => a.remove();
      a.appendChild(x);
    });
  }

  /* ---------- konfirmasi tengah untuk form/link destruktif ---------- */
  function ambilPesanConfirm(el) {
    const raw = el.getAttribute('data-confirm') || el.getAttribute('onsubmit') || el.getAttribute('onclick') || '';
    const m = raw.match(/confirm\s*\(\s*['"](.+?)['"]\s*\)/);
    if (m) return m[1];
    if (raw && raw.indexOf('confirm') === -1) return raw;
    return '';
  }
  function bindConfirmTengah() {
    // Link hapus (?act=hapus) / link data-confirm -> dialog tengah, bukan confirm() polos
    $$('a[href*="act=hapus"], a[data-confirm]').forEach((a) => {
      if (a._plConfirmBound) return;
      a._plConfirmBound = true;
      // buang handler polos bawaan (onclick="return confirm(...)") agar tidak dobel
      const legacy = ambilPesanConfirm(a);
      if (legacy) a.setAttribute('data-confirm', legacy);
      a.removeAttribute('onclick');
      a.addEventListener('click', (ev) => {
        ev.preventDefault();
        ev.stopPropagation();
        const custom = a.getAttribute('data-confirm') || '';
        // cari nama data dari baris tabel terdekat agar pesan lebih jelas
        let nama = '';
        const tr = a.closest ? a.closest('tr') : null;
        if (tr) {
          const b = tr.querySelector('td b');
          if (b) nama = b.textContent.trim();
        }
        const label = nama ? '"' + nama + '"' : ((a.textContent || '').trim() || 'Data ini');
        confirmBox('Hapus Data?', (custom || (label + ' akan dihapus permanen dan tidak bisa dibatalkan.')), 'Ya, Hapus', 'Batal')
          .then((ya) => { if (ya) window.location.href = a.href; });
      });
    });
    // Tombol/link lain dengan onclick confirm() polos (mis. tombol Tolak) -> dialog tengah
    $$('a[onclick*="confirm"], button[onclick*="confirm"]').forEach((el) => {
      if (el._plConfirmBound || el.closest('a[href*="act=hapus"]')) return;
      el._plConfirmBound = true;
      const pesan = ambilPesanConfirm(el) || 'Lanjutkan aksi ini?';
      el.removeAttribute('onclick');
      el.setAttribute('data-confirm-btn', pesan);
      el.addEventListener('click', (ev) => {
        const form = el.closest ? el.closest('form') : null;
        // jika tombol submit di dalam form yang juga punya konfirmasi,
        // biarkan handler form yang menangani (hindari dialog dobel)
        if (form && (form.hasAttribute('data-confirm') || (form.getAttribute('onsubmit') || '').indexOf('confirm') !== -1)) return;
        ev.preventDefault();
        ev.stopPropagation();
        confirmBox('Yakin?', pesan, 'Ya, Lanjutkan', 'Batal')
          .then((ya) => {
            if (!ya) return;
            if (el.tagName === 'A' && el.href) window.location.href = el.href;
            else if (form) {
              form._plConfirmOk = true;
              if (el.name && el.value) {
                let hid = form.querySelector('input[type="hidden"][name="' + el.name + '"]');
                if (!hid) {
                  hid = document.createElement('input');
                  hid.type = 'hidden';
                  hid.name = el.name;
                  form.appendChild(hid);
                }
                hid.value = el.value;
              }
              if (typeof form.requestSubmit === 'function' && el.type === 'submit') {
                try { form.requestSubmit(el); return; } catch (err) {}
              }
              form.submit();
            }
          });
      });
    });
    // Form dengan data-confirm="pesan" ATAU onsubmit="return confirm(...)" -> dialog tengah
    $$('form[data-confirm], form[onsubmit*="confirm"]').forEach((f) => {
      if (f._plConfirmBound) return;
      f._plConfirmBound = true;
      const legacy = ambilPesanConfirm(f);
      if (legacy && !f.getAttribute('data-confirm')) f.setAttribute('data-confirm', legacy);
      f.removeAttribute('onsubmit');
      // fallback pelacak tombol submit (untuk browser tanpa ev.submitter)
      f.addEventListener('click', (ev) => {
        const b = ev.target && ev.target.closest ? ev.target.closest('button[type="submit"], input[type="submit"]') : null;
        if (b) f._lastBtn = b;
      });
      f.addEventListener('submit', (ev) => {
        if (f._plConfirmOk) { f._plConfirmOk = false; return; }
        ev.preventDefault();
        // tombol submit bisa punya pesan sendiri (mis. Tolak vs Terima)
        const sub = ev.submitter || f._lastBtn || document.activeElement;
        const pesanBtn = sub && sub.getAttribute ? (sub.getAttribute('data-confirm-btn') || sub.getAttribute('data-confirm')) : '';
        const pesan = pesanBtn || f.getAttribute('data-confirm') || 'Lanjutkan aksi ini?';
        const judul = /hapus|tolak|batal/i.test(pesan) ? 'Yakin Diteruskan?' : 'Yakin?';
        confirmBox(judul, pesan, 'Ya, Lanjutkan', 'Batal')
          .then((ya) => {
            if (ya) {
              f._plConfirmOk = true;
              if (sub && sub.name && sub.value) {
                let hid = f.querySelector('input[type="hidden"][name="' + sub.name + '"]');
                if (!hid) {
                  hid = document.createElement('input');
                  hid.type = 'hidden';
                  hid.name = sub.name;
                  f.appendChild(hid);
                }
                hid.value = sub.value;
              }
              if (typeof f.requestSubmit === 'function' && sub && sub.form === f) {
                try { f.requestSubmit(sub); return; } catch (err) {}
              }
              f.submit();
            }
          });
      });
    });
  }
  document.addEventListener('DOMContentLoaded', function () {
    // pulihkan tipe tersimpan
    try {
      const t = getTipe();
      const tw = $('#tipeTakeaway'), di = $('#tipeDineIn');
      if (tw) tw.classList.toggle('on', t === 'takeaway');
      if (di) di.classList.toggle('on', t === 'dine_in');
      $$('input[name="tipe_order"]').forEach((r) => { r.checked = (r.value === t); });
    } catch (e) {}
    updateBadgeSilent();
    if ($('#cartBody')) renderCheckoutTable();
    if ($('input[name="metode_bayar"]')) togglePay();
    updateMenuCount();
    popupFlashServer();
    bindConfirmTengah();
    const m = $('#modalCustom');
    if (m) m.addEventListener('click', function (e) {
      if (e.target === m) closeCustomizer();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeCustomizer();
      // shortcut "/" fokus ke search ala Stitch
      if (e.key === '/' && !/INPUT|TEXTAREA/.test((document.activeElement || {}).tagName || '')) {
        const s = $('#qDesktop') || $('#qMobile');
        if (s) { e.preventDefault(); s.focus(); }
      }
    });
    window.addEventListener('resize', function () {
      const fab = $('#fabBar');
      if (fab) {
        const n = cartCount(getCart());
        fab.classList.toggle('show', n > 0 && window.innerWidth < 768);
      }
    });
    // jam admin realtime
    const jam = $('#jamLive');
    if (jam) setInterval(() => {
      jam.textContent = new Date().toLocaleString('id-ID', {
        weekday: 'short', day: '2-digit', month: 'short',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
      });
    }, 1000);
  });
})();
