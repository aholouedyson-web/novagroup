<?php
// ============================================================
//  NOVAGROUP — Point d'entrée principal
// ============================================================
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} elseif (file_exists(__DIR__ . '/includes/config.php')) {
    require_once __DIR__ . '/includes/config.php';
}
startSession();

$page     = $_GET['page'] ?? 'home';
$user     = currentUser();
$isAdmin  = $user && $user['is_admin'];

// Redirection automatique
if ($user && $page === 'login')    $page = $isAdmin ? 'admin' : 'dashboard';
if ($user && $page === 'register') $page = $isAdmin ? 'admin' : 'dashboard';
if (!$user && in_array($page, ['dashboard','admin','withdraw','wd-form'])) $page = 'login';
if ($user && !$isAdmin && $page === 'admin') $page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NovaGroup — Banque Digitale de Confiance</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* ══════════════════════════════════════
   DESIGN TOKENS
══════════════════════════════════════ */
:root {
  --ink:       #0d1b2e;
  --ink2:      #122240;
  --ink3:      #1a2f50;
  --gold:      #b8973a;
  --gold-lt:   #d4b05a;
  --gold-pale: rgba(184,151,58,.12);
  --gold-bdr:  rgba(184,151,58,.22);
  --sky:       #1e4db7;
  --white:     #ffffff;
  --offwhite:  #f5f7fa;
  --muted:     #7a8fa6;
  --muted2:    #b0c0d4;
  --danger:    #d94040;
  --success:   #1e9e5e;
  --warn:      #d97706;
  --glass:     rgba(255,255,255,.05);
  --glass2:    rgba(255,255,255,.08);
  --radius-sm: 8px;
  --radius:    14px;
  --radius-lg: 22px;
  --shadow:    0 8px 32px rgba(0,0,0,.28);
  --shadow-sm: 0 2px 12px rgba(0,0,0,.18);
  --transition: .25s ease;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth;font-size:16px}
body{font-family:'Inter',sans-serif;background:var(--ink);color:var(--white);min-height:100vh;overflow-x:hidden;line-height:1.6}
img{display:block;max-width:100%;height:auto}
a{text-decoration:none}
button{font-family:'Inter',sans-serif;cursor:pointer}
::-webkit-scrollbar{width:5px}
::-webkit-scrollbar-track{background:var(--ink)}
::-webkit-scrollbar-thumb{background:var(--gold);border-radius:4px}

/* NAV */
.nav{position:fixed;top:0;left:0;right:0;z-index:900;display:flex;align-items:center;justify-content:space-between;padding:0 64px;height:70px;background:rgba(13,27,46,.96);backdrop-filter:blur(20px) saturate(180%);border-bottom:1px solid var(--gold-bdr);transition:box-shadow .3s}
.nav.scrolled{box-shadow:0 4px 24px rgba(0,0,0,.4)}
.nav-logo{font-family:'Playfair Display',serif;font-size:22px;font-weight:900;color:var(--gold);letter-spacing:.5px;display:flex;align-items:center;gap:8px;cursor:pointer}
.nav-logo-icon{width:34px;height:34px;background:var(--gold-pale);border:1px solid var(--gold-bdr);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px}
.nav-logo span{color:var(--white)}
.nav-links{display:flex;align-items:center;gap:6px}
.nav-links a{color:var(--muted);font-size:14px;font-weight:500;padding:6px 14px;border-radius:8px;transition:all var(--transition);position:relative;cursor:pointer}
.nav-links a::after{content:'';position:absolute;bottom:2px;left:50%;right:50%;height:2px;background:var(--gold);border-radius:2px;transition:all .25s ease}
.nav-links a:hover{color:var(--white);background:var(--glass2)}
.nav-links a:hover::after{left:14px;right:14px}
.nav-actions{display:flex;align-items:center;gap:10px}
.btn-ghost{padding:8px 20px;border:1.5px solid var(--gold-bdr);color:var(--gold-lt);background:transparent;border-radius:var(--radius-sm);font-size:14px;font-weight:500;transition:all var(--transition)}
.btn-ghost:hover{border-color:var(--gold);background:var(--gold-pale);color:var(--gold)}
.btn-cta{padding:8px 22px;border:none;background:var(--gold);color:var(--ink);border-radius:var(--radius-sm);font-size:14px;font-weight:700;transition:all var(--transition);box-shadow:0 4px 14px rgba(184,151,58,.35)}
.btn-cta:hover{background:var(--gold-lt);transform:translateY(-1px);box-shadow:0 6px 20px rgba(184,151,58,.45)}
.burger{display:none;flex-direction:column;gap:5px;background:none;border:none;padding:6px;cursor:pointer}
.burger span{display:block;width:24px;height:2px;background:var(--white);border-radius:2px;transition:all .3s ease}
.burger.open span:nth-child(1){transform:translateY(7px) rotate(45deg)}
.burger.open span:nth-child(2){opacity:0;transform:scaleX(0)}
.burger.open span:nth-child(3){transform:translateY(-7px) rotate(-45deg)}
.mobile-menu{display:none;position:fixed;top:70px;left:0;right:0;bottom:0;background:rgba(13,27,46,.98);backdrop-filter:blur(20px);z-index:800;padding:32px 28px;flex-direction:column;gap:8px;transform:translateX(100%);transition:transform .3s ease}
.mobile-menu.open{transform:translateX(0);display:flex}
.mobile-menu a{color:var(--muted2);font-size:17px;font-weight:500;padding:14px 18px;border-radius:var(--radius);border:1px solid transparent;transition:all var(--transition);cursor:pointer}
.mobile-menu a:hover{background:var(--glass2);color:var(--white);border-color:var(--gold-bdr)}
.mobile-menu-actions{display:flex;flex-direction:column;gap:10px;margin-top:24px;border-top:1px solid var(--gold-bdr);padding-top:24px}

/* SECTIONS */
.section{padding:100px 64px}
.section-inner{max-width:1180px;margin:0 auto}
.section-label{font-size:11px;letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:14px;font-weight:600}
.section-title{font-family:'Playfair Display',serif;font-size:42px;font-weight:800;line-height:1.15;margin-bottom:16px}
.section-title em{color:var(--gold);font-style:normal}
.section-sub{font-size:16px;color:var(--muted);max-width:520px;line-height:1.7}
.section-head-center{text-align:center;margin-bottom:64px}
.section-head-center .section-sub{margin:0 auto}

/* HERO */
.hero{min-height:100vh;display:flex;align-items:center;position:relative;overflow:hidden;padding-top:70px}
.hero-bg{position:absolute;inset:0;background:url('https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?w=1920&q=80&auto=format&fit=crop') center/cover no-repeat;filter:brightness(.18) saturate(1.2)}
.hero-tint{position:absolute;inset:0;background:linear-gradient(135deg,rgba(13,27,46,.9) 0%,rgba(30,77,183,.12) 50%,rgba(13,27,46,.85) 100%)}
.hero-dots{position:absolute;inset:0;background-image:radial-gradient(rgba(184,151,58,.18) 1px,transparent 1px);background-size:38px 38px}
.hero-inner{position:relative;z-index:2;max-width:1180px;margin:0 auto;padding:0 64px;display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:72px}
.hero-tag{display:inline-flex;align-items:center;gap:8px;background:var(--gold-pale);border:1px solid var(--gold-bdr);padding:5px 14px;border-radius:50px;font-size:11px;font-weight:600;color:var(--gold-lt);letter-spacing:1.8px;text-transform:uppercase;margin-bottom:24px}
.hero-tag .dot{width:6px;height:6px;background:var(--gold);border-radius:50%;animation:blink 2s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
.hero-h1{font-family:'Playfair Display',serif;font-size:54px;font-weight:900;line-height:1.1;margin-bottom:20px;letter-spacing:-.5px}
.hero-h1 em{color:var(--gold);font-style:normal}
.hero-desc{font-size:16px;color:var(--muted2);line-height:1.75;margin-bottom:36px;max-width:460px}
.hero-btns{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:48px}
.btn-hero-primary{padding:14px 34px;background:var(--gold);color:var(--ink);border:none;border-radius:var(--radius);font-size:15px;font-weight:700;box-shadow:0 8px 28px rgba(184,151,58,.4);transition:all .3s;cursor:pointer}
.btn-hero-primary:hover{background:var(--gold-lt);transform:translateY(-2px);box-shadow:0 12px 36px rgba(184,151,58,.5)}
.btn-hero-secondary{padding:14px 34px;background:transparent;border:1.5px solid rgba(255,255,255,.25);color:var(--white);border-radius:var(--radius);font-size:15px;font-weight:500;transition:all .3s;cursor:pointer}
.btn-hero-secondary:hover{border-color:var(--gold-lt);color:var(--gold-lt);background:var(--gold-pale)}
.hero-kpi{display:flex;gap:40px}
.kpi-num{font-family:'Playfair Display',serif;font-size:32px;font-weight:900;color:var(--gold);line-height:1}
.kpi-label{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1.5px;margin-top:4px}
.hero-visual{display:flex;flex-direction:column;gap:18px;align-items:flex-end}
.floating-card{width:340px;border-radius:20px;padding:28px;position:relative;overflow:hidden;animation:float 5s ease-in-out infinite}
@keyframes float{0%,100%{transform:translateY(0) rotate(-1deg)}50%{transform:translateY(-12px) rotate(-1deg)}}
.card-premium{background:linear-gradient(135deg,#1a3a8c 0%,#0d234f 100%);border:1px solid rgba(184,151,58,.35);box-shadow:0 32px 64px rgba(0,0,0,.5),0 0 0 1px rgba(255,255,255,.05)}
.card-chip{width:38px;height:28px;background:linear-gradient(135deg,var(--gold),var(--gold-lt));border-radius:5px;margin-bottom:28px}
.card-num{font-size:14px;letter-spacing:3.5px;color:rgba(255,255,255,.8);margin-bottom:20px;font-family:'Inter',monospace}
.card-row{display:flex;justify-content:space-between;align-items:center}
.card-holder-label{font-size:9px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1.5px}
.card-holder-name{font-size:13px;font-weight:600;letter-spacing:.5px;margin-top:2px}
.card-brand{font-family:'Playfair Display',serif;font-size:20px;font-weight:900;color:var(--gold)}
.card-secondary{width:290px;border-radius:17px;padding:22px;background:linear-gradient(135deg,#17432b 0%,#0b2417 100%);border:1px solid rgba(46,196,106,.25);box-shadow:0 18px 48px rgba(0,0,0,.4);margin-right:-20px;opacity:.85;animation:float2 5s 1.5s ease-in-out infinite}
@keyframes float2{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}

/* TRUST BAR */
.trust-bar{background:rgba(184,151,58,.06);border-top:1px solid var(--gold-bdr);border-bottom:1px solid var(--gold-bdr);padding:18px 64px;display:flex;align-items:center;justify-content:space-around;gap:16px;flex-wrap:wrap}
.trust-item{display:flex;align-items:center;gap:9px;font-size:13px;color:var(--muted);font-weight:500}
.trust-item .ti-icon{font-size:18px}
.trust-item strong{color:var(--muted2)}

/* FEATURES */
.features-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:64px}
.feat-card{background:var(--glass);border:1px solid var(--gold-bdr);border-radius:var(--radius-lg);padding:34px;transition:all .35s;position:relative;overflow:hidden}
.feat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--gold),transparent);opacity:0;transition:.35s}
.feat-card:hover{transform:translateY(-6px);border-color:rgba(184,151,58,.45);background:var(--glass2)}
.feat-card:hover::before{opacity:1}
.feat-icon{width:52px;height:52px;background:var(--gold-pale);border:1px solid var(--gold-bdr);border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:20px}
.feat-card h3{font-size:17px;font-weight:700;margin-bottom:10px;font-family:'Playfair Display',serif}
.feat-card p{font-size:14px;color:var(--muted);line-height:1.7}

/* STATS BAND */
.stats-band{position:relative;overflow:hidden;background:url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1600&q=80&auto=format&fit=crop') center/cover}
.stats-band::before{content:'';position:absolute;inset:0;background:rgba(13,27,46,.9)}
.stats-band-inner{position:relative;z-index:1;display:grid;grid-template-columns:repeat(4,1fr);gap:0;text-align:center}
.stat-col{padding:60px 20px;border-right:1px solid var(--gold-bdr)}
.stat-col:last-child{border-right:none}
.stat-big{font-family:'Playfair Display',serif;font-size:44px;font-weight:900;color:var(--gold);line-height:1;margin-bottom:8px}
.stat-lbl{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:2px}

/* TESTIMONIALS */
.testi-section{background:var(--ink2)}
.testi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
.testi-card{background:var(--ink3);border:1px solid rgba(255,255,255,.07);border-radius:var(--radius-lg);padding:30px;transition:all .35s;position:relative}
.testi-card:hover{transform:translateY(-5px);border-color:var(--gold-bdr);box-shadow:var(--shadow)}
.testi-quote{font-size:40px;color:var(--gold-pale);font-family:'Playfair Display',serif;line-height:1;margin-bottom:16px}
.testi-text{font-size:14px;color:var(--muted2);line-height:1.8;margin-bottom:22px;font-style:italic}
.testi-footer{display:flex;align-items:center;gap:14px;padding-top:18px;border-top:1px solid rgba(255,255,255,.06)}
.testi-avatar{width:52px;height:52px;border-radius:50%;overflow:hidden;flex-shrink:0;border:2px solid var(--gold-bdr)}
.testi-avatar img{width:100%;height:100%;object-fit:cover}
.testi-name{font-size:14px;font-weight:700;margin-bottom:2px}
.testi-location{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:5px}
.testi-stars{display:flex;gap:2px;margin-bottom:4px}
.testi-stars span{color:var(--gold);font-size:12px}
.testi-badge{position:absolute;top:20px;right:20px;background:var(--gold-pale);border:1px solid var(--gold-bdr);border-radius:50px;padding:3px 10px;font-size:10px;color:var(--gold-lt);font-weight:600;letter-spacing:1px;text-transform:uppercase}

/* APP SECTION */
.app-section{background:url('https://images.unsplash.com/photo-1563986768609-322da13575f3?w=1600&q=80&auto=format&fit=crop') center/cover;position:relative;overflow:hidden}
.app-section::before{content:'';position:absolute;inset:0;background:rgba(13,27,46,.88)}
.app-section-inner{position:relative;z-index:1;display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:80px}

/* FOOTER */
footer{background:var(--ink2);border-top:1px solid var(--gold-bdr);padding:48px 64px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px}
.footer-logo{font-family:'Playfair Display',serif;font-size:20px;color:var(--gold);font-weight:900}
.footer-links{display:flex;gap:24px}
.footer-links a{color:var(--muted);font-size:13px;transition:color var(--transition)}
.footer-links a:hover{color:var(--gold-lt)}
.footer-copy{font-size:12px;color:var(--muted);width:100%;text-align:center;padding-top:20px;border-top:1px solid rgba(255,255,255,.05);margin-top:8px}

/* AUTH */
.auth-wrap{min-height:100vh;display:grid;grid-template-columns:1fr 1fr;position:relative}
.auth-left{position:relative;overflow:hidden;background:url('https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=900&q=80&auto=format&fit=crop') center/cover;display:flex;flex-direction:column;justify-content:flex-end;padding:48px}
.auth-left::before{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(13,27,46,.95) 0%,rgba(13,27,46,.4) 60%,transparent 100%)}
.auth-left-content{position:relative;z-index:1}
.auth-left h2{font-family:'Playfair Display',serif;font-size:36px;font-weight:900;margin-bottom:12px}
.auth-left p{color:var(--muted2);font-size:15px;line-height:1.7;max-width:380px}
.auth-left-trust{display:flex;flex-direction:column;gap:12px;margin-top:28px}
.auth-trust-item{display:flex;align-items:center;gap:10px;font-size:13px;color:var(--muted2)}
.auth-trust-item .ati-icon{width:30px;height:30px;background:var(--gold-pale);border:1px solid var(--gold-bdr);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
.auth-right{background:var(--ink2);padding:48px 56px;display:flex;align-items:center;justify-content:center;padding-top:80px}
.auth-form-wrap{width:100%;max-width:440px}
.auth-form-logo{font-family:'Playfair Display',serif;font-size:20px;color:var(--gold);font-weight:900;margin-bottom:32px;display:flex;align-items:center;gap:8px}
.auth-form-title{font-family:'Playfair Display',serif;font-size:26px;font-weight:800;margin-bottom:6px}
.auth-form-sub{font-size:14px;color:var(--muted);margin-bottom:30px}
.form-field{margin-bottom:16px}
.form-field label{display:block;font-size:11px;font-weight:700;color:var(--muted2);letter-spacing:1px;text-transform:uppercase;margin-bottom:7px}
.form-field input,.form-field select{width:100%;padding:11px 15px;background:rgba(255,255,255,.05);border:1.5px solid rgba(255,255,255,.1);border-radius:var(--radius-sm);color:var(--white);font-family:'Inter',sans-serif;font-size:14px;outline:none;transition:all var(--transition)}
.form-field input:focus,.form-field select:focus{border-color:var(--gold);background:rgba(184,151,58,.05)}
.form-field input.error{border-color:var(--danger)}
.form-field .err-msg{font-size:11px;color:var(--danger);margin-top:5px;display:none}
.form-field .err-msg.show{display:block}
.form-field select option{background:var(--ink2);color:var(--white)}
.form-row2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.currency-hint{padding:10px 14px;background:var(--gold-pale);border:1px solid var(--gold-bdr);border-radius:var(--radius-sm);font-size:13px;color:var(--gold-lt);margin-bottom:14px;margin-top:-6px;display:none}
.btn-form{width:100%;padding:13px;background:var(--gold);border:none;border-radius:var(--radius-sm);color:var(--ink);font-family:'Inter',sans-serif;font-size:15px;font-weight:700;cursor:pointer;transition:all .3s;margin-top:8px;box-shadow:0 4px 16px rgba(184,151,58,.3)}
.btn-form:hover{background:var(--gold-lt);transform:translateY(-1px)}
.btn-form:disabled{opacity:.6;cursor:not-allowed;transform:none}
.form-footer-link{text-align:center;margin-top:18px;font-size:13px;color:var(--muted)}
.form-footer-link a{color:var(--gold-lt);cursor:pointer}

/* DASHBOARD */
.dash-layout{padding-top:70px;min-height:100vh;display:grid;grid-template-columns:256px 1fr}
.dash-sidebar{background:var(--ink2);border-right:1px solid var(--gold-bdr);padding:0;height:calc(100vh - 70px);position:sticky;top:70px;display:flex;flex-direction:column}
.dash-sidebar-logo{font-family:'Playfair Display',serif;font-size:18px;color:var(--gold);font-weight:900;padding:28px 26px 22px;border-bottom:1px solid var(--gold-bdr)}
.dash-nav{padding:14px 12px;flex:1}
.dash-nav-item{display:flex;align-items:center;gap:12px;padding:11px 14px;border-radius:var(--radius-sm);color:var(--muted);font-size:14px;font-weight:500;transition:all var(--transition);cursor:pointer;border:none;background:none;width:100%;margin-bottom:3px}
.dash-nav-item .ni-icon{font-size:17px;flex-shrink:0}
.dash-nav-item:hover{color:var(--white);background:var(--glass2)}
.dash-nav-item.active{color:var(--gold-lt);background:var(--gold-pale);border:1px solid var(--gold-bdr);font-weight:600}
.dash-sidebar-user{padding:18px 20px;border-top:1px solid var(--gold-bdr);display:flex;align-items:center;gap:12px}
.suser-av{width:36px;height:36px;border-radius:50%;background:var(--gold);display:flex;align-items:center;justify-content:center;color:var(--ink);font-weight:800;font-size:15px;flex-shrink:0}
.suser-name{font-size:13px;font-weight:600}
.suser-email{font-size:11px;color:var(--muted)}
.dash-main{padding:36px 48px;overflow-y:auto}
.dash-main h1{font-family:'Playfair Display',serif;font-size:28px;margin-bottom:4px}
.dash-main .ds{color:var(--muted);font-size:14px;margin-bottom:28px}
.balance-card{background:linear-gradient(145deg,#163070 0%,#0d2058 100%);border:1px solid rgba(184,151,58,.3);border-radius:var(--radius-lg);padding:34px;margin-bottom:24px;position:relative;overflow:hidden}
.balance-card::after{content:'';position:absolute;top:-60px;right:-60px;width:240px;height:240px;background:radial-gradient(circle,rgba(184,151,58,.1),transparent 70%);border-radius:50%;pointer-events:none}
.bc-label{font-size:11px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:2px;margin-bottom:8px}
.bc-amount{font-family:'Playfair Display',serif;font-size:46px;font-weight:900;color:var(--gold);line-height:1;margin-bottom:4px}
.bc-currency{font-size:13px;color:rgba(255,255,255,.45);margin-bottom:22px}
.bc-actions{display:flex;gap:12px;flex-wrap:wrap}
.bc-btn{padding:11px 24px;border-radius:var(--radius-sm);font-size:13px;font-weight:600;transition:all .25s;cursor:pointer}
.bc-btn-gold{background:var(--gold);color:var(--ink);border:none;box-shadow:0 4px 14px rgba(184,151,58,.3)}
.bc-btn-gold:hover{background:var(--gold-lt);transform:translateY(-1px)}
.bc-btn-outline{background:transparent;border:1.5px solid rgba(184,151,58,.35);color:var(--gold-lt)}
.bc-btn-outline:hover{border-color:var(--gold);background:var(--gold-pale)}
.bc-btn-continue{background:linear-gradient(135deg,#b45309,#d97706);color:var(--white);border:none;animation:pulse-warn 2s infinite}
@keyframes pulse-warn{0%,100%{box-shadow:0 4px 14px rgba(217,119,6,.3)}50%{box-shadow:0 4px 24px rgba(217,119,6,.6)}}
.pending-banner{background:rgba(217,119,6,.1);border:1px solid rgba(217,119,6,.35);border-radius:var(--radius);padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}
.pb-info{display:flex;align-items:center;gap:12px}
.pb-icon{font-size:26px}
.pb-title{font-size:14px;font-weight:700;color:var(--warn)}
.pb-sub{font-size:12px;color:var(--muted);margin-top:1px}
.mini-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:22px}
.ms-card{background:var(--glass);border:1px solid var(--gold-bdr);border-radius:var(--radius);padding:20px}
.ms-label{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:6px}
.ms-val{font-family:'Playfair Display',serif;font-size:19px;font-weight:700}
.ms-sub{font-size:11px;color:var(--muted);margin-top:3px}
.txs{background:var(--glass);border:1px solid var(--gold-bdr);border-radius:var(--radius-lg);padding:24px}
.txs h3{font-family:'Playfair Display',serif;font-size:18px;margin-bottom:18px}
.tx-empty{text-align:center;padding:28px;color:var(--muted);font-size:14px}

/* WITHDRAWAL FORM PAGE */
.wd-form-page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:100px 20px 40px;position:relative}
.wd-form-bg{position:absolute;inset:0;background:url('https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?w=1400&q=80&auto=format&fit=crop') center/cover;filter:brightness(.1)}
.wd-form-box{position:relative;z-index:1;width:100%;max-width:580px;background:rgba(18,34,64,.96);border:1px solid var(--gold-bdr);border-radius:var(--radius-lg);padding:44px;box-shadow:0 40px 80px rgba(0,0,0,.5);backdrop-filter:blur(20px)}
.wd-form-header{margin-bottom:28px}
.wd-form-header h2{font-family:'Playfair Display',serif;font-size:26px;margin-bottom:6px}
.wd-form-header p{font-size:13px;color:var(--muted)}
.wd-form-amount-badge{background:var(--gold-pale);border:1px solid var(--gold-bdr);border-radius:var(--radius);padding:16px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between}
.wfab-label{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:1.5px}
.wfab-amount{font-family:'Playfair Display',serif;font-size:28px;color:var(--gold);font-weight:900}
.form-section-title{font-size:11px;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:1.5px;margin:20px 0 14px;padding-bottom:8px;border-bottom:1px solid rgba(255,255,255,.07)}
.security-note{display:flex;align-items:flex-start;gap:10px;background:rgba(30,158,94,.08);border:1px solid rgba(30,158,94,.25);border-radius:var(--radius-sm);padding:13px 15px;margin-bottom:20px;font-size:13px}
.security-note .sn-icon{font-size:18px;flex-shrink:0;margin-top:1px}
.security-note p{color:var(--muted2);line-height:1.55}

/* WITHDRAWAL PROGRESS */
.wd-progress-page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:100px 20px 40px;position:relative}
.wd-progress-bg{position:absolute;inset:0;background:url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=1400&q=80&auto=format&fit=crop') center/cover;filter:brightness(.08)}
.wd-progress-box{position:relative;z-index:1;width:100%;max-width:540px;background:rgba(18,34,64,.97);border:1px solid var(--gold-bdr);border-radius:var(--radius-lg);padding:44px;box-shadow:0 40px 80px rgba(0,0,0,.55);backdrop-filter:blur(20px)}
.wd-progress-box h2{font-family:'Playfair Display',serif;font-size:24px;margin-bottom:6px}
.wd-progress-box .wd-sub{font-size:13px;color:var(--muted);margin-bottom:26px}
.wd-amount-badge{background:var(--gold-pale);border:1px solid var(--gold-bdr);border-radius:var(--radius);padding:18px;text-align:center;margin-bottom:24px}
.wab-label{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:2px;margin-bottom:6px}
.wab-amount{font-family:'Playfair Display',serif;font-size:36px;color:var(--gold);font-weight:900}
.progress-wrap{margin-bottom:18px}
.progress-top{display:flex;justify-content:space-between;margin-bottom:8px;font-size:13px;color:var(--muted)}
.progress-pct{color:var(--gold);font-weight:700;font-size:16px}
.progress-track{height:9px;background:rgba(255,255,255,.07);border-radius:9px;overflow:hidden;position:relative}
.progress-bar{height:100%;background:linear-gradient(90deg,var(--gold),var(--gold-lt));width:0%;border-radius:9px;transition:width .3s ease;box-shadow:0 0 12px rgba(184,151,58,.5)}
.progress-sheen{position:absolute;top:0;bottom:0;width:40px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.35),transparent);animation:sheen 1.8s linear infinite}
@keyframes sheen{from{left:-40px}to{left:110%}}
.wd-dots{display:flex;gap:7px;justify-content:center;margin:14px 0}
.wd-dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.12);transition:.3s}
.wd-dot.done{background:var(--success)}
.wd-dot.active{background:var(--gold);box-shadow:0 0 8px rgba(184,151,58,.6)}
.wd-status-text{text-align:center;font-size:12px;color:var(--muted);margin-bottom:18px}
.code-box{display:none;background:rgba(217,64,64,.07);border:1px solid rgba(217,64,64,.3);border-radius:var(--radius);padding:22px;text-align:center;margin-bottom:16px}
.code-box.show{display:block}
.code-box .cb-icon{font-size:36px;margin-bottom:12px}
.code-box h3{font-family:'Playfair Display',serif;font-size:17px;color:var(--gold-lt);margin-bottom:8px}
.code-box p{font-size:13px;color:var(--muted);line-height:1.65;margin-bottom:16px}
.code-input{width:100%;padding:13px;background:rgba(255,255,255,.05);border:2px solid rgba(217,64,64,.4);border-radius:var(--radius-sm);color:var(--white);font-family:'Inter',monospace;font-size:24px;text-align:center;letter-spacing:8px;font-weight:700;outline:none;transition:border .3s}
.code-input:focus{border-color:var(--gold)}
.btn-validate{width:100%;padding:12px;margin-top:12px;background:var(--gold);border:none;border-radius:var(--radius-sm);color:var(--ink);font-family:'Inter',sans-serif;font-size:14px;font-weight:700;cursor:pointer;transition:all .3s}
.btn-validate:hover{background:var(--gold-lt)}
.wd-foot{display:flex;justify-content:space-between;align-items:center;margin-top:16px;flex-wrap:wrap;gap:10px}
.btn-save-leave{background:var(--gold-pale);border:1px solid var(--gold-bdr);color:var(--gold-lt);padding:9px 18px;border-radius:var(--radius-sm);font-size:13px;font-weight:600;transition:.3s;cursor:pointer}
.btn-save-leave:hover{background:rgba(184,151,58,.2)}
.btn-cancel-wd{background:none;border:none;color:var(--muted);font-size:13px;cursor:pointer;text-decoration:underline;text-underline-offset:3px}
.fail-screen{display:none;text-align:center;padding:28px 0}
.fail-screen.show{display:block}
.fail-screen .fi{font-size:56px;margin-bottom:14px}
.fail-screen h2{font-family:'Playfair Display',serif;font-size:24px;color:var(--danger);margin-bottom:10px}
.fail-screen p{font-size:14px;color:var(--muted);line-height:1.7;margin-bottom:20px}
.fail-screen .err-code{font-size:11px;color:rgba(255,255,255,.2);margin-bottom:20px}
.btn-retry{padding:11px 30px;background:transparent;border:1.5px solid var(--gold);color:var(--gold-lt);border-radius:var(--radius-sm);font-size:14px;font-weight:600;cursor:pointer;transition:.3s}
.btn-retry:hover{background:var(--gold-pale)}

/* ADMIN */
.admin-table-wrap{background:var(--glass);border:1px solid var(--gold-bdr);border-radius:var(--radius-lg);overflow:auto}
.admin-table{width:100%;border-collapse:collapse}
.admin-table th{padding:12px 16px;text-align:left;font-size:10px;color:var(--gold-lt);text-transform:uppercase;letter-spacing:1.5px;background:var(--gold-pale);border-bottom:1px solid var(--gold-bdr);white-space:nowrap}
.admin-table td{padding:13px 16px;font-size:13px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle}
.admin-table tr:last-child td{border-bottom:none}
.admin-table tr:hover td{background:rgba(255,255,255,.02)}
.tag-active{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:rgba(30,158,94,.15);color:var(--success)}
.tbl-btn{padding:5px 11px;border-radius:6px;border:none;font-size:12px;font-family:'Inter',sans-serif;font-weight:600;cursor:pointer;transition:.25s}
.tbl-btn.edit{background:rgba(184,151,58,.15);color:var(--gold-lt)}
.tbl-btn.edit:hover{background:rgba(184,151,58,.3)}
.tbl-btn.del{background:rgba(217,64,64,.12);color:var(--danger)}
.tbl-btn.del:hover{background:rgba(217,64,64,.25)}
.admin-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
.akpi{background:var(--glass);border:1px solid var(--gold-bdr);border-radius:var(--radius);padding:18px}
.akpi .ak-num{font-family:'Playfair Display',serif;font-size:26px;color:var(--gold);font-weight:800}
.akpi .ak-lbl{font-size:11px;color:var(--muted);margin-top:3px}
.codes-board{background:var(--glass);border:1px solid rgba(217,64,64,.3);border-radius:var(--radius-lg);padding:22px;margin-top:22px}
.codes-board h3{font-family:'Playfair Display',serif;font-size:18px;color:var(--gold-lt);margin-bottom:16px}
.code-row{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:rgba(217,64,64,.05);border:1px solid rgba(217,64,64,.18);border-radius:var(--radius-sm);margin-bottom:10px;flex-wrap:wrap;gap:10px}
.code-row-info .cr-name{font-size:13px;font-weight:600}
.code-row-info .cr-meta{font-size:11px;color:var(--muted);margin-top:2px}
.code-row-val{font-family:'Playfair Display',serif;font-size:24px;font-weight:900;color:var(--danger);letter-spacing:5px}

/* MODALS */
.modal-mask{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:2000;align-items:center;justify-content:center;backdrop-filter:blur(6px)}
.modal-mask.show{display:flex}
.modal-body{background:var(--ink2);border:1px solid var(--gold-bdr);border-radius:var(--radius-lg);padding:34px;width:90%;max-width:400px;animation:fadeUp .3s ease}
@keyframes fadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
.modal-body h3{font-family:'Playfair Display',serif;font-size:20px;margin-bottom:12px}
.modal-actions{display:flex;gap:10px;margin-top:20px;justify-content:flex-end}
.mbt{padding:9px 20px;border-radius:var(--radius-sm);font-family:'Inter',sans-serif;font-size:13px;font-weight:600;cursor:pointer;transition:.25s}
.mbt.cancel{background:transparent;border:1.5px solid rgba(255,255,255,.12);color:var(--muted)}
.mbt.confirm{background:var(--gold);border:none;color:var(--ink)}
.mbt.danger{background:rgba(217,64,64,.12);border:1.5px solid var(--danger);color:var(--danger)}

/* TOAST */
.toast-stack{position:fixed;bottom:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px}
.t-item{padding:13px 18px;border-radius:var(--radius);font-size:14px;font-weight:500;box-shadow:var(--shadow);animation:toastIn .3s ease;max-width:320px;display:flex;align-items:center;gap:10px}
@keyframes toastIn{from{opacity:0;transform:translateX(36px)}to{opacity:1;transform:translateX(0)}}
.t-item.success{background:#1a4c36;color:#6ee7b7;border-left:3px solid var(--success)}
.t-item.error{background:#4c1a1a;color:#fca5a5;border-left:3px solid var(--danger)}
.t-item.info{background:#3d320f;color:#fde68a;border-left:3px solid var(--gold)}

/* RESPONSIVE */
@media(max-width:1100px){
  .nav{padding:0 30px}.hero-inner{padding:0 30px;gap:40px}.section{padding:80px 30px}
  footer{padding:40px 30px}.auth-wrap{grid-template-columns:1fr}.auth-left{display:none}
  .auth-right{padding:80px 30px 40px}.stats-band-inner{grid-template-columns:1fr 1fr}
  .stat-col:nth-child(2){border-right:none}.features-grid{grid-template-columns:1fr 1fr}
  .testi-grid{grid-template-columns:1fr 1fr}.app-section-inner{grid-template-columns:1fr;gap:40px}
}
@media(max-width:768px){
  .nav{padding:0 18px}.nav-links,.nav-actions{display:none}.burger{display:flex}
  .hero-inner{grid-template-columns:1fr;padding:0 20px}.hero-visual{display:none}
  .hero-h1{font-size:36px}.hero-kpi{gap:24px}.kpi-num{font-size:26px}
  .section{padding:60px 20px}.section-title{font-size:32px}
  .features-grid{grid-template-columns:1fr}.testi-grid{grid-template-columns:1fr}
  .trust-bar{padding:18px 20px}
  footer{padding:30px 20px;flex-direction:column;text-align:center}
  .footer-links{flex-wrap:wrap;justify-content:center;gap:14px}
  .dash-layout{grid-template-columns:1fr}.dash-sidebar{display:none}
  .dash-main{padding:20px 16px}.mini-stats{grid-template-columns:1fr}
  .admin-kpis{grid-template-columns:1fr 1fr}.stats-band-inner{grid-template-columns:1fr 1fr}
}
@media(max-width:480px){
  .hero-h1{font-size:30px}.hero-btns{flex-direction:column}
  .wd-form-box,.wd-progress-box{padding:28px 20px}
}
</style>
</head>
<body>

<!-- NAV -->
<nav class="nav" id="main-nav">
  <div class="nav-logo" onclick="goTo('home')">
    <div class="nav-logo-icon"><i class="fa-solid fa-gem"></i></div>
    Nova<span>Group</span>
  </div>
  <div class="nav-links">
    <a onclick="scrollToId('features')">Services</a>
    <a onclick="scrollToId('testimonials')">Avis clients</a>
    <a onclick="scrollToId('app-section')">L'application</a>
    <a onclick="goTo('home')">À propos</a>
  </div>
  <div class="nav-actions">
    <?php if ($user): ?>
      <?php if ($isAdmin): ?>
        <button class="btn-ghost" onclick="goTo('admin')">Panel Admin</button>
      <?php else: ?>
        <button class="btn-ghost" onclick="goTo('dashboard')">Mon compte</button>
      <?php endif; ?>
      <button class="btn-cta" onclick="doLogout()">Déconnexion</button>
    <?php else: ?>
      <button class="btn-ghost" onclick="goTo('login')">Connexion</button>
      <button class="btn-cta" onclick="goTo('register')">Ouvrir un compte</button>
    <?php endif; ?>
  </div>
  <button class="burger" id="burger" onclick="toggleMobile()" aria-label="Menu">
    <span></span><span></span><span></span>
  </button>
</nav>

<div class="mobile-menu" id="mobile-menu">
  <a onclick="scrollToId('features');closeMobile()">Services</a>
  <a onclick="scrollToId('testimonials');closeMobile()">Avis clients</a>
  <a onclick="scrollToId('app-section');closeMobile()">L'application</a>
  <div class="mobile-menu-actions">
    <?php if ($user): ?>
      <button class="btn-cta" style="width:100%;padding:13px;font-size:15px;" onclick="doLogout()">Déconnexion</button>
    <?php else: ?>
      <button class="btn-ghost" style="width:100%;padding:13px;font-size:15px;" onclick="goTo('login');closeMobile()">Connexion</button>
      <button class="btn-cta" style="width:100%;padding:13px;font-size:15px;" onclick="goTo('register');closeMobile()">Ouvrir un compte</button>
    <?php endif; ?>
  </div>
</div>

<!-- ═══ PAGES ═══ -->

<?php if ($page === 'home'): ?>
<!-- ═══════════════════════════════════ HOME ═══════════════════════════════════ -->
<section class="hero">
  <div class="hero-bg"></div><div class="hero-tint"></div><div class="hero-dots"></div>
  <div class="hero-inner">
    <div>
      <div class="hero-tag"><div class="dot"></div> Banque Digitale Certifiée BCE</div>
      <h1 class="hero-h1">Gérez votre argent<br>avec <em>confiance</em><br>et liberté</h1>
      <p class="hero-desc">NovaGroup vous offre une expérience bancaire complète, sécurisée et moderne. Virements instantanés, solde en temps réel, depuis n'importe quel appareil, dans 52 pays.</p>
      <div class="hero-btns">
        <button class="btn-hero-primary" onclick="goTo('register')">Ouvrir un compte gratuit</button>
        <button class="btn-hero-secondary" onclick="scrollToId('features')">Découvrir nos services</button>
      </div>
      <div class="hero-kpi">
        <div class="kpi-item"><div class="kpi-num">2M+</div><div class="kpi-label">Clients actifs</div></div>
        <div class="kpi-item"><div class="kpi-num">€4.2B</div><div class="kpi-label">Volume traité</div></div>
        <div class="kpi-item"><div class="kpi-num">52</div><div class="kpi-label">Pays couverts</div></div>
      </div>
    </div>
    <div class="hero-visual">
      <div class="floating-card card-premium">
        <div class="card-chip"></div>
        <div class="card-num">4829 •••• •••• 7731</div>
        <div class="card-row">
          <div class="card-holder">
            <div class="card-holder-label">Card Holder</div>
            <div class="card-holder-name">NOVA PREMIUM</div>
          </div>
          <div class="card-brand">NG</div>
        </div>
      </div>
      <div class="floating-card card-secondary" style="height:160px">
        <div class="card-chip" style="background:linear-gradient(135deg,#22c55e,#16a34a);width:34px;height:24px;margin-bottom:20px;"></div>
        <div class="card-num" style="font-size:13px;color:rgba(255,255,255,.6);margin-bottom:10px;">7743 •••• •••• 9920</div>
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <span style="font-size:11px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:1px;">NOVA SILVER</span>
          <span style="font-family:'Playfair Display',serif;font-size:16px;color:#22c55e;font-weight:900;">NG</span>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="trust-bar">
  <div class="trust-item"><span class="ti-icon"><i class="fa-solid fa-lock"></i></span><strong>SSL 256-bit</strong></div>
  <div class="trust-item"><span class="ti-icon"><i class="fa-solid fa-landmark"></i></span><strong>Agréé BCE</strong></div>
  <div class="trust-item"><span class="ti-icon"><i class="fa-solid fa-shield-halved"></i></span><strong>Fonds garantis 100 000 €</strong></div>
  <div class="trust-item"><span class="ti-icon"><i class="fa-solid fa-bolt"></i></span><strong>Transactions temps réel</strong></div>
  <div class="trust-item"><span class="ti-icon"><i class="fa-solid fa-earth-europe"></i></span><strong>52 pays desservis</strong></div>
  <div class="trust-item"><span class="ti-icon"><i class="fa-solid fa-mobile-screen"></i></span><strong>App mobile incluse</strong></div>
</div>

<section class="section" id="features">
  <div class="section-inner">
    <div class="section-head-center">
      <div class="section-label">Pourquoi NovaGroup</div>
      <h2 class="section-title">Des services conçus pour <em>votre avenir</em></h2>
      <p class="section-sub">Tout ce dont vous avez besoin pour gérer vos finances au quotidien, en un seul endroit.</p>
    </div>
    <div class="features-grid">
      <div class="feat-card"><div class="feat-icon"><i class="fa-solid fa-building-columns"></i></div><h3>Compte Multi-Devises</h3><p>Euros, Dollars, Livres... gérez plusieurs devises depuis un seul compte, sans frais cachés.</p></div>
      <div class="feat-card"><div class="feat-icon"><i class="fa-solid fa-lock"></i></div><h3>Sécurité Maximale</h3><p>Chiffrement de bout en bout, authentification multi-facteurs et surveillance 24/7 de chaque transaction.</p></div>
      <div class="feat-card"><div class="feat-icon"><i class="fa-solid fa-bolt"></i></div><h3>Virements Instantanés</h3><p>Transférez de l'argent vers n'importe quel compte bancaire dans le monde en quelques secondes.</p></div>
      <div class="feat-card"><div class="feat-icon"><i class="fa-solid fa-chart-line"></i></div><h3>Tableau de Bord Intelligent</h3><p>Visualisez vos dépenses, suivez vos économies et prenez de meilleures décisions financières.</p></div>
      <div class="feat-card"><div class="feat-icon"><i class="fa-solid fa-credit-card"></i></div><h3>Carte Virtuelle & Physique</h3><p>Recevez votre carte NovaGroup premium en moins de 5 jours, utilisable dans le monde entier.</p></div>
      <div class="feat-card"><div class="feat-icon"><i class="fa-solid fa-circle-check"></i></div><h3>Zéro Frais d'Ouverture</h3><p>Ouvrez votre compte en 5 minutes, sans frais. Commencez à gérer votre argent immédiatement.</p></div>
    </div>
  </div>
</section>

<div class="stats-band section" style="padding:0">
  <div class="stats-band-inner section-inner" style="max-width:100%">
    <div class="stat-col"><div class="stat-big">€4.2B</div><div class="stat-lbl">Volume traité</div></div>
    <div class="stat-col"><div class="stat-big">2M+</div><div class="stat-lbl">Clients actifs</div></div>
    <div class="stat-col"><div class="stat-big">99.98%</div><div class="stat-lbl">Disponibilité</div></div>
    <div class="stat-col"><div class="stat-big">52</div><div class="stat-lbl">Pays desservis</div></div>
  </div>
</div>

<section class="section testi-section" id="testimonials">
  <div class="section-inner">
    <div class="section-head-center">
      <div class="section-label">Témoignages vérifiés</div>
      <h2 class="section-title">Nos clients <em>européens</em> parlent</h2>
      <p class="section-sub">Des milliers de clients à travers l'Europe font confiance à NovaGroup pour gérer leur argent au quotidien.</p>
    </div>
    <div class="testi-grid">
      <?php
      $testimonials = [
        ['Pierre Martin','Paris, France','🇫🇷','men/32','NovaGroup a complètement transformé ma façon de gérer mes finances. Les virements sont instantanés, j\'ai reçu mon prêt de 15 000 euros en 48h.'],
        ['Andreea Popescu','Bucarest, Roumanie','🇷🇴','women/44','Après plusieurs refus ailleurs, cette banque m\'a donné une chance. Leur équipe a pris le temps d\'étudier mon dossier et m\'a proposé une solution adaptée.'],
        ['Klaus Müller','Berlin, Allemagne','🇩🇪','men/55','Le délai de traitement a été très rapide. J\'ai reçu les fonds directement sur mon compte et j\'ai pu gérer mes urgences sans stress!'],
        ['Zsuzsanna Kovács','Budapest, Hongrie','🇭🇺','women/67','Avec Nova Group j\'ai reçu un prêt de 7000 euros en moins de 72h, Service professionnel et sécurisé.'],
        ['Marko Horvat','Zagreb, Croatie','🇭🇷','men/76','NovaGroup est une plateforme incroyable. Les transactions sont instantanées, l\'interface est intuitive et la sécurité est au plus haut niveau.'],
        ['Sophie Dubois','Lyon, France','🇫🇷','women/29','Après avoir testé plusieurs néobanques, NovaGroup se distingue par sa fiabilité et son design soigné. Les frais sont transparents.'],
      ];
      foreach ($testimonials as $t): ?>
      <div class="testi-card">
        <div class="testi-badge">Vérifié ✓</div>
        <div class="testi-quote">"</div>
        <p class="testi-text"><?= h($t[4]) ?></p>
        <div class="testi-footer">
          <div class="testi-avatar"><img src="https://randomuser.me/api/portraits/<?= $t[3] ?>.jpg" alt="<?= h($t[0]) ?>" loading="lazy"></div>
          <div>
            <div class="testi-stars"><span>★</span><span>★</span><span>★</span><span>★</span><span>★</span></div>
            <div class="testi-name"><?= h($t[0]) ?></div>
            <div class="testi-location"><span><?= $t[2] ?></span> <?= h($t[1]) ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section app-section" id="app-section">
  <div class="section-inner">
    <div class="app-section-inner">
      <div>
        <div class="section-label">L'application mobile</div>
        <h2 class="section-title" style="max-width:440px">Votre banque dans <em>votre poche</em></h2>
        <p class="section-sub" style="margin-bottom:28px">Gérez vos comptes, effectuez des virements et consultez vos transactions où que vous soyez. Application disponible sur iOS et Android.</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <button class="btn-hero-primary" style="font-size:14px;padding:12px 26px"><i class="fa-brands fa-apple" style="margin-right:7px"></i>App Store</button>
          <button class="btn-hero-secondary" style="font-size:14px;padding:12px 26px"><i class="fa-brands fa-google-play" style="margin-right:7px"></i>Google Play</button>
        </div>
      </div>
      <div style="text-align:center">
        <img src="https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=500&q=80&auto=format&fit=crop" alt="Application mobile NovaGroup" style="max-width:320px;border-radius:24px;box-shadow:0 40px 80px rgba(0,0,0,.6);border:1px solid rgba(255,255,255,.1);margin:0 auto" loading="lazy">
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="footer-logo">Nova<span style="color:var(--white)">Group</span></div>
  <div class="footer-links">
    <a href="#">Conditions d'utilisation</a>
    <a href="#">Politique de confidentialité</a>
    <a href="#">Support</a>
    <a href="#">Contact</a>
  </div>
  <div class="footer-copy">© 2025 NovaGroup SA · Agréé par les Autorités Financières Européennes · Siège : Luxembourg</div>
</footer>

<?php elseif ($page === 'register'): ?>
<!-- ═══ REGISTER ═══ -->
<div class="auth-wrap">
  <div class="auth-left">
    <div class="auth-left-content">
      <h2>Bienvenue chez NovaGroup</h2>
      <p>Rejoignez 2 millions de clients qui font confiance à NovaGroup pour gérer leur argent partout dans le monde.</p>
      <div class="auth-left-trust">
        <div class="auth-trust-item"><div class="ati-icon"><i class="fa-solid fa-lock"></i></div> Données chiffrées SSL 256-bit</div>
        <div class="auth-trust-item"><div class="ati-icon"><i class="fa-solid fa-landmark"></i></div> Agréé Banque Centrale Européenne</div>
        <div class="auth-trust-item"><div class="ati-icon"><i class="fa-solid fa-shield-halved"></i></div> Fonds garantis jusqu'à 100 000 €</div>
        <div class="auth-trust-item"><div class="ati-icon"><i class="fa-solid fa-bolt"></i></div> Ouverture de compte en 5 minutes</div>
      </div>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-form-wrap">
      <div class="auth-form-logo"><i class="fa-solid fa-gem"></i> Nova<span style="color:var(--white)">Group</span></div>
      <div class="auth-form-title">Créer un compte</div>
      <div class="auth-form-sub">Remplissez le formulaire ci-dessous pour ouvrir votre compte gratuitement.</div>
      <div class="form-row2">
        <div class="form-field"><label>Prénom *</label><input type="text" id="reg-fn" placeholder=""></div>
        <div class="form-field"><label>Nom de famille *</label><input type="text" id="reg-ln" placeholder=""></div>
      </div>
      <div class="form-field"><label>Date de naissance *</label><input type="date" id="reg-dob" max="<?= date('Y-m-d', strtotime('-18 years')) ?>"></div>
      <div class="form-field"><label>Adresse e-mail *</label><input type="email" id="reg-email" placeholder=""></div>
      <div class="form-field"><label>Mot de passe *</label><input type="password" id="reg-pw" placeholder="Minimum 6 caractères"></div>
      <div class="form-field"><label>Téléphone *</label><input type="tel" id="reg-phone" placeholder=""></div>
      <div class="form-field">
        <label>Pays de résidence *</label>
        <select id="reg-country" onchange="updateCurrencyPreview()">
          <option value="">-- Sélectionner votre pays --</option>
          <optgroup label="🌍 Europe">
            <option value="FR">🇫🇷 France</option><option value="DE">🇩🇪 Allemagne</option>
            <option value="IT">🇮🇹 Italie</option><option value="ES">🇪🇸 Espagne</option>
            <option value="BE">🇧🇪 Belgique</option><option value="NL">🇳🇱 Pays-Bas</option>
            <option value="PT">🇵🇹 Portugal</option><option value="RO">🇷🇴 Roumanie</option>
            <option value="HR">🇭🇷 Croatie</option><option value="HU">🇭🇺 Hongrie</option>
            <option value="CH">🇨🇭 Suisse</option><option value="GB">🇬🇧 Royaume-Uni</option>
            <option value="PL">🇵🇱 Pologne</option><option value="SE">🇸🇪 Suède</option>
          </optgroup>
          <optgroup label="🌎 Amériques">
            <option value="US">🇺🇸 États-Unis</option><option value="CA">🇨🇦 Canada</option>
            <option value="MX">🇲🇽 Mexique</option><option value="BR">🇧🇷 Brésil</option>
            <option value="AR">🇦🇷 Argentine</option><option value="CO">🇨🇴 Colombie</option>
          </optgroup>
          <optgroup label="🌍 Afrique">
            <option value="BJ">🇧🇯 Bénin</option><option value="SN">🇸🇳 Sénégal</option>
            <option value="CI">🇨🇮 Côte d'Ivoire</option><option value="CM">🇨🇲 Cameroun</option>
            <option value="NG">🇳🇬 Nigeria</option><option value="GH">🇬🇭 Ghana</option>
            <option value="MA">🇲🇦 Maroc</option>
          </optgroup>
          <optgroup label="🌏 Asie / Moyen-Orient">
            <option value="AE">🇦🇪 Émirats Arabes Unis</option>
            <option value="JP">🇯🇵 Japon</option><option value="IN">🇮🇳 Inde</option>
          </optgroup>
        </select>
      </div>
      <div class="currency-hint" id="currency-hint"><i class="fa-solid fa-coins" style="margin-right:6px"></i>Devise attribuée : <strong id="currency-name"></strong></div>
      <div class="form-field"><label>IBAN / Numéro de compte *</label><input type="text" id="reg-iban" placeholder=""></div>
      <div class="form-field"><label>Nom de la banque *</label><input type="text" id="reg-bank" placeholder=""></div>
      <button class="btn-form" id="reg-btn" onclick="registerUser()">Créer mon compte NovaGroup →</button>
      <div class="form-footer-link">Déjà client ? <a onclick="goTo('login')">Se connecter</a> · <a onclick="goTo('home')">Retour à l'accueil</a></div>
    </div>
  </div>
</div>

<?php elseif ($page === 'login'): ?>
<!-- ═══ LOGIN ═══ -->
<div class="auth-wrap">
  <div class="auth-left" style="background-image:url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=900&q=80&auto=format&fit=crop')">
    <div class="auth-left-content">
      <h2>Content de vous revoir</h2>
      <p>Connectez-vous à votre espace bancaire sécurisé NovaGroup pour gérer vos finances.</p>
      <div class="auth-left-trust">
        <div class="auth-trust-item"><div class="ati-icon"><i class="fa-solid fa-lock"></i></div> Connexion sécurisée TLS 1.3</div>
        <div class="auth-trust-item"><div class="ati-icon"><i class="fa-solid fa-mobile-screen"></i></div> Double authentification disponible</div>
        <div class="auth-trust-item"><div class="ati-icon"><i class="fa-solid fa-shield-halved"></i></div> Session chiffrée de bout en bout</div>
      </div>
    </div>
  </div>
  <div class="auth-right">
    <div class="auth-form-wrap">
      <div class="auth-form-logo"><i class="fa-solid fa-gem"></i> Nova<span style="color:var(--white)">Group</span></div>
      <div class="auth-form-title">Connexion</div>
      <div class="auth-form-sub">Accédez à votre espace bancaire sécurisé.</div>
      <div class="form-field"><label>Adresse e-mail</label><input type="email" id="login-email" placeholder=""></div>
      <div class="form-field"><label>Mot de passe</label><input type="password" id="login-pw" placeholder="" onkeydown="if(event.key==='Enter')loginUser()"></div>
      <button class="btn-form" id="login-btn" onclick="loginUser()">Se connecter →</button>
      <div class="form-footer-link">Pas encore client ? <a onclick="goTo('register')">Créer un compte</a> · <a onclick="goTo('home')">Retour</a></div>
    </div>
  </div>
</div>

<?php elseif ($page === 'dashboard'): ?>
<!-- ═══ DASHBOARD ═══ -->
<?php
$u = requireLogin();
$sym = currencySymbol($u['currency']);
$pendingStmt = db()->prepare("SELECT * FROM withdrawals WHERE user_id=? AND status='pending' ORDER BY id DESC LIMIT 1");
$pendingStmt->execute([$u['id']]);
$pending = $pendingStmt->fetch() ?: null;
?>
<div class="dash-layout">
  <aside class="dash-sidebar">
    <div class="dash-sidebar-logo"><i class="fa-solid fa-gem"></i> Nova<span style="color:var(--white)">Group</span></div>
    <nav class="dash-nav">
      <button class="dash-nav-item active" id="sn-overview" onclick="showDashTab('tab-overview')"><span class="ni-icon"><i class="fa-solid fa-house"></i></span> Vue d'ensemble</button>
      <button class="dash-nav-item" id="sn-withdraw" onclick="showDashTab('tab-withdraw')"><span class="ni-icon"><i class="fa-solid fa-money-bill-wave"></i></span> Retrait</button>
      <button class="dash-nav-item" id="sn-profile" onclick="showDashTab('tab-profile')"><span class="ni-icon"><i class="fa-solid fa-user"></i></span> Mon Profil</button>
      <button class="dash-nav-item" onclick="doLogout()" style="margin-top:auto"><span class="ni-icon"><i class="fa-solid fa-right-from-bracket"></i></span> Déconnexion</button>
    </nav>
    <div class="dash-sidebar-user">
      <div class="suser-av"><?= strtoupper(substr($u['first_name'], 0, 1)) ?></div>
      <div>
        <div class="suser-name"><?= h($u['first_name'] . ' ' . $u['last_name']) ?></div>
        <div class="suser-email"><?= h($u['email']) ?></div>
      </div>
    </div>
  </aside>
  <main class="dash-main">

    <!-- OVERVIEW -->
    <div id="tab-overview" class="dash-tab">
      <h1>Bonjour, <?= h($u['first_name']) ?> 👋</h1>
      <p class="ds">Voici un aperçu de votre compte NovaGroup</p>

      <?php if ($pending): ?>
      <div class="pending-banner">
        <div class="pb-info">
          <div class="pb-icon"><i class="fa-solid fa-clock"></i></div>
          <div>
            <div class="pb-title">Retrait en cours non terminé</div>
            <div class="pb-sub"><?= fmtAmount($pending['amount'], $pending['currency']) ?> — Étape <?= $pending['step'] + 1 ?>/5 en cours</div>
          </div>
        </div>
        <button class="bc-btn bc-btn-continue" onclick="resumeWithdrawal(<?= $pending['id'] ?>,<?= $pending['amount'] ?>,'<?= h($pending['currency']) ?>',<?= $pending['step'] ?>,<?= $pending['current_pct'] ?>,<?= (int)$pending['code_requested'] ?>)">▶ Continuer le retrait</button>
      </div>
      <?php endif; ?>

      <div class="balance-card">
        <div class="bc-label">Solde disponible</div>
        <div class="bc-amount"><?= fmtAmount($u['balance']) ?> <?= h($sym) ?></div>
        <div class="bc-currency"><?= h($u['currency']) ?> — <?= h(currencyName($u['currency'])) ?></div>
        <div class="bc-actions">
          <button class="bc-btn bc-btn-gold" onclick="showDashTab('tab-withdraw')"><i class="fa-solid fa-money-bill-wave" style="margin-right:6px"></i>Effectuer un retrait</button>
          <button class="bc-btn bc-btn-outline"><i class="fa-solid fa-file-lines" style="margin-right:6px"></i>Relevé de compte</button>
        </div>
      </div>

      <div class="mini-stats">
        <div class="ms-card">
          <div class="ms-label">Compte principal</div>
          <div class="ms-val" style="font-size:14px;"><?= h(maskIban($u['iban'])) ?></div>
          <div class="ms-sub"><?= h($u['bank_name']) ?></div>
        </div>
        <div class="ms-card">
          <div class="ms-label">Statut</div>
          <div class="ms-val" style="color:var(--success)">● Actif</div>
          <div class="ms-sub">Compte vérifié ✓</div>
        </div>
        <div class="ms-card">
          <div class="ms-label">Pays / Devise</div>
          <div class="ms-val" style="font-size:16px;"><?= h($u['country']) ?></div>
          <div class="ms-sub"><?= h($sym . ' · ' . $u['currency']) ?></div>
        </div>
      </div>

      <div class="txs">
        <h3>Historique des transactions</h3>
        <?php
        $txStmt = db()->prepare("SELECT * FROM withdrawals WHERE user_id=? ORDER BY id DESC LIMIT 20");
        $txStmt->execute([$u['id']]);
        $txs = $txStmt->fetchAll();
        if ($txs): ?>
          <div style="display:flex;flex-direction:column;gap:8px">
          <?php foreach ($txs as $tx): 
            $statusColor = ['pending'=>'#d97706','completed'=>'#1e9e5e','failed'=>'#d94040','cancelled'=>'#7a8fa6'][$tx['status']] ?? '#7a8fa6';
            $statusLabel = ['pending'=>'En cours','completed'=>'Complété','failed'=>'Échoué','cancelled'=>'Annulé'][$tx['status']] ?? $tx['status'];
          ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:var(--radius-sm)">
            <div style="display:flex;align-items:center;gap:12px">
              <div style="width:38px;height:38px;background:var(--gold-pale);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0"><i class="fa-solid fa-money-bill-wave"></i></div>
              <div>
                <div style="font-size:14px;font-weight:600">Retrait</div>
                <div style="font-size:11px;color:var(--muted)"><?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?></div>
              </div>
            </div>
            <div style="text-align:right">
              <div style="font-family:'Playfair Display',serif;font-size:16px;color:var(--gold)">-<?= fmtAmount($tx['amount'], $tx['currency']) ?></div>
              <div style="font-size:11px;color:<?= $statusColor ?>;font-weight:600"><?= $statusLabel ?></div>
            </div>
          </div>
          <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="tx-empty">Aucune transaction pour le moment.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- WITHDRAW TAB -->
    <div id="tab-withdraw" class="dash-tab" style="display:none">
      <h1>Effectuer un retrait</h1>
      <p class="ds">Transférez des fonds vers votre compte bancaire principal enregistré</p>
      <?php if ($pending): ?>
      <div class="pending-banner" style="margin-bottom:20px">
        <div class="pb-info">
          <div class="pb-icon"><i class="fa-solid fa-clock"></i></div>
          <div>
            <div class="pb-title">Retrait non terminé</div>
            <div class="pb-sub"><?= fmtAmount($pending['amount'], $pending['currency']) ?> — Étape <?= $pending['step'] + 1 ?>/5</div>
          </div>
        </div>
        <button class="bc-btn bc-btn-continue" onclick="resumeWithdrawal(<?= $pending['id'] ?>,<?= $pending['amount'] ?>,'<?= h($pending['currency']) ?>',<?= $pending['step'] ?>,<?= $pending['current_pct'] ?>,<?= (int)$pending['code_requested'] ?>)">▶ Continuer</button>
      </div>
      <?php endif; ?>
      <div style="max-width:480px">
        <div class="form-field"><label>Montant à retirer</label><input type="number" id="wd-amount" placeholder="0.00" min="1"></div>
        <div class="form-field">
          <label>Compte destinataire (IBAN)</label>
          <input type="text" value="<?= h(maskIban($u['iban'])) ?>" readonly style="opacity:.6;cursor:not-allowed">
        </div>
        <div style="padding:13px 15px;background:rgba(184,151,58,.07);border:1px solid var(--gold-bdr);border-radius:var(--radius-sm);margin-bottom:20px;font-size:13px;color:var(--muted);line-height:1.6"><i class="fa-solid fa-triangle-exclamation" style="margin-right:6px;color:var(--warn)"></i>Les retraits sont traités sous 1 à 3 jours ouvrables. Une validation de sécurité multi-étapes est requise.</div>
        <button class="btn-form" onclick="initiateWD()" style="max-width:300px">Initier le retrait →</button>
      </div>
    </div>

    <!-- PROFILE TAB -->
    <div id="tab-profile" class="dash-tab" style="display:none">
      <h1>Mon Profil</h1>
      <p class="ds">Vos informations personnelles et coordonnées bancaires</p>
      <div style="background:var(--glass);border:1px solid var(--gold-bdr);border-radius:var(--radius-lg);padding:30px;max-width:580px">
        <?php
        $rows = [
          ['Nom complet', $u['first_name'] . ' ' . $u['last_name']],
          ['Date de naissance', $u['dob'] ? date('d/m/Y', strtotime($u['dob'])) : '—'],
          ['E-mail', $u['email']],
          ['Téléphone', $u['phone']],
          ['Pays', $u['country']],
          ['IBAN', $u['iban']],
          ['Banque', $u['bank_name']],
          ['Devise', currencyName($u['currency'])],
          ['Solde', '<strong style="color:var(--gold)">' . fmtAmount($u['balance'], $u['currency']) . '</strong>'],
          ['Membre depuis', date('d/m/Y', strtotime($u['created_at']))],
        ];
        ?>
        <div style="display:grid;gap:12px">
          <?php foreach ($rows as [$label, $val]): ?>
          <div style="display:flex;gap:12px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.05)">
            <span style="color:var(--muted);min-width:155px;font-size:13px"><?= h($label) ?></span>
            <span style="font-size:13px"><?= $val ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- WD FORM (inline modal approach) -->
<div id="wd-form-overlay" style="display:none;position:fixed;inset:0;z-index:500;overflow-y:auto">
  <div class="wd-form-page" style="position:relative;min-height:100vh">
    <div class="wd-form-bg"></div>
    <div class="wd-form-box">
      <div class="wd-form-header">
        <h2><i class="fa-solid fa-building-columns" style="margin-right:8px;color:var(--gold)"></i>Coordonnées bancaires</h2>
        <p>Veuillez confirmer vos coordonnées bancaires pour traiter le retrait en toute sécurité.</p>
      </div>
      <div class="wd-form-amount-badge">
        <div><div class="wfab-label">Montant du retrait</div><div class="wfab-amount" id="wdf-amount-display">0,00</div></div>
        <div style="font-size:28px"><i class="fa-solid fa-money-bill-transfer" style="color:var(--gold)"></i></div>
      </div>
      <div class="security-note">
        <div class="sn-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <p>Vos coordonnées bancaires sont chiffrées et ne seront jamais partagées avec des tiers. Cette étape est obligatoire pour valider chaque virement.</p>
      </div>
      <div class="form-section-title">Informations du bénéficiaire</div>
      <div class="form-row2">
        <div class="form-field">
          <label>Prénom du titulaire *</label>
          <input type="text" id="wdf-firstname" value="<?= h($u['first_name']) ?>">
          <div class="err-msg" id="wdf-firstname-err">Ce champ est obligatoire</div>
        </div>
        <div class="form-field">
          <label>Nom du titulaire *</label>
          <input type="text" id="wdf-lastname" value="<?= h($u['last_name']) ?>">
          <div class="err-msg" id="wdf-lastname-err">Ce champ est obligatoire</div>
        </div>
      </div>
      <div class="form-section-title">Coordonnées bancaires</div>
      <div class="form-field">
        <label>Nom de la banque *</label>
        <input type="text" id="wdf-bankname" value="<?= h($u['bank_name']) ?>">
        <div class="err-msg" id="wdf-bankname-err">Ce champ est obligatoire</div>
      </div>
      <div class="form-field">
        <label>IBAN *</label>
        <input type="text" id="wdf-iban" value="<?= h($u['iban']) ?>">
        <div class="err-msg" id="wdf-iban-err">Veuillez entrer un IBAN valide</div>
      </div>
      <div class="form-row2">
        <div class="form-field">
          <label>Code BIC / SWIFT *</label>
          <input type="text" id="wdf-bic" placeholder="BNPAFRPP">
          <div class="err-msg" id="wdf-bic-err">Ce champ est obligatoire</div>
        </div>
        <div class="form-field">
          <label>Numéro de téléphone *</label>
          <input type="tel" id="wdf-phone" value="<?= h($u['phone']) ?>">
          <div class="err-msg" id="wdf-phone-err">Ce champ est obligatoire</div>
        </div>
      </div>
      <div class="form-field">
        <label>Motif du retrait</label>
        <input type="text" id="wdf-reason" placeholder="Ex : retrait personnel, investissement...">
      </div>
      <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:20px;margin-top:6px">
        <input type="checkbox" id="wdf-confirm" style="margin-top:3px;accent-color:var(--gold);width:16px;height:16px;flex-shrink:0">
        <label for="wdf-confirm" style="font-size:13px;color:var(--muted);cursor:pointer;line-height:1.55">Je certifie que ces coordonnées bancaires m'appartiennent et sont exactes. Je comprends que toute erreur peut entraîner un échec du virement.</label>
      </div>
      <button class="btn-form" onclick="submitBankDetails()">Confirmer et lancer le virement →</button>
      <div style="text-align:center;margin-top:14px"><a onclick="closeWdForm()" style="font-size:13px;color:var(--muted);cursor:pointer">← Retour au tableau de bord</a></div>
    </div>
  </div>
</div>

<!-- PROGRESS OVERLAY -->
<div id="wd-progress-overlay" style="display:none;position:fixed;inset:0;z-index:600;overflow-y:auto">
  <div class="wd-progress-page" style="position:relative;min-height:100vh">
    <div class="wd-progress-bg"></div>
    <div class="wd-progress-box">
      <div id="wd-main-ui">
        <h2>Traitement du retrait</h2>
        <p class="wd-sub">Votre virement est en cours de traitement sécurisé. Veuillez ne pas fermer cette page.</p>
        <div class="wd-amount-badge">
          <div class="wab-label">Montant du virement</div>
          <div class="wab-amount" id="wd-display-amount">0,00</div>
        </div>
        <div class="progress-wrap">
          <div class="progress-top"><span>Progression</span><span class="progress-pct" id="pct-label">0%</span></div>
          <div class="progress-track"><div class="progress-bar" id="progress-bar"></div><div class="progress-sheen"></div></div>
        </div>
        <div class="wd-dots">
          <div class="wd-dot" id="dot-0"></div><div class="wd-dot" id="dot-1"></div>
          <div class="wd-dot" id="dot-2"></div><div class="wd-dot" id="dot-3"></div>
          <div class="wd-dot" id="dot-4"></div>
        </div>
        <div class="wd-status-text" id="wd-step-info">Initialisation...</div>
        <div class="code-box" id="code-box">
          <div class="cb-icon"><i class="fa-solid fa-key"></i></div>
          <h3>Code de confirmation requis</h3>
          <p>Veuillez remplir le code de confirmation afin de continuer le traitement du virement. Ce code a été transmis à votre gestionnaire de compte NovaGroup. Contactez-le pour l'obtenir.</p>
          <input type="text" class="code-input" id="code-input" placeholder="• • • • • •" maxlength="6">
          <button class="btn-validate" onclick="validateCode()">Valider le code →</button>
        </div>
        <div class="wd-foot">
          <button class="btn-save-leave" onclick="saveAndLeave()"><i class="fa-solid fa-floppy-disk" style="margin-right:6px"></i>Sauvegarder et quitter</button>
          <button class="btn-cancel-wd" onclick="cancelWithdrawal()">Annuler le virement</button>
        </div>
      </div>
      <div class="fail-screen" id="fail-screen">
        <div class="fi"><i class="fa-solid fa-circle-xmark" style="color:var(--danger)"></i></div>
        <h2>Virement échoué</h2>
        <p>Votre virement n'a pas pu être complété en raison d'une erreur de validation bancaire. Veuillez réessayer ultérieurement ou contacter le support NovaGroup.</p>
        <div class="err-code">Code erreur : NG-TXN-5503</div>
        <button class="btn-retry" onclick="retryWithdrawal()">↩ Réessayer</button>
        <div style="margin-top:14px"><a onclick="closeProgress()" style="color:var(--muted);font-size:13px;cursor:pointer;text-decoration:underline">← Tableau de bord</a></div>
      </div>
    </div>
  </div>
</div>

<?php elseif ($page === 'admin'): ?>
<!-- ═══ ADMIN ═══ -->
<?php $admin = requireAdmin(); ?>
<div class="dash-layout">
  <aside class="dash-sidebar">
    <div class="dash-sidebar-logo" style="color:#e74c3c">Nova<span style="color:var(--white)">Admin</span></div>
    <nav class="dash-nav">
      <button class="dash-nav-item active" id="an-users" onclick="showAdminTab('tab-users')"><span class="ni-icon"><i class="fa-solid fa-users"></i></span> Utilisateurs</button>
      <button class="dash-nav-item" id="an-codes" onclick="showAdminTab('tab-codes')">
        <span class="ni-icon"><i class="fa-solid fa-key"></i></span> Codes retrait
        <span id="codes-badge" style="background:var(--danger);color:#fff;border-radius:50%;min-width:18px;height:18px;display:inline-flex;align-items:center;justify-content:center;font-size:10px;margin-left:auto;padding:0 4px;">0</span>
      </button>
      <button class="dash-nav-item" onclick="doLogout()"><span class="ni-icon"><i class="fa-solid fa-right-from-bracket"></i></span> Déconnexion</button>
    </nav>
    <div class="dash-sidebar-user">
      <div class="suser-av" style="background:var(--danger)">A</div>
      <div><div class="suser-name">Administrateur</div><div class="suser-email">admin@novagroup.com</div></div>
    </div>
  </aside>
  <main class="dash-main">
    <div id="tab-users" class="admin-tab">
      <h1>Gestion des Utilisateurs</h1>
      <p class="ds" style="margin-bottom:20px">Gérez les comptes, modifiez les soldes et supprimez les utilisateurs.</p>
      <div class="admin-kpis" id="admin-kpis"><div style="grid-column:span 4;color:var(--muted)">Chargement...</div></div>
      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead><tr><th>Utilisateur</th><th>Email</th><th>Naissance</th><th>Pays</th><th>Solde</th><th>Devise</th><th>Statut</th><th>Actions</th></tr></thead>
          <tbody id="users-tbody"><tr><td colspan="8" style="text-align:center;padding:28px;color:var(--muted)">Chargement...</td></tr></tbody>
        </table>
      </div>
    </div>
    <div id="tab-codes" class="admin-tab" style="display:none">
      <h1>Codes de Validation des Retraits</h1>
      <p class="ds">Communiquez ces codes aux utilisateurs pour valider chaque étape de leur retrait.</p>
      <div class="codes-board"><h3><i class="fa-solid fa-key" style="margin-right:7px"></i>Codes en attente</h3><div id="codes-list">Chargement...</div></div>
    </div>
  </main>
</div>
<?php endif; ?>

<!-- MODALS -->
<div class="modal-mask" id="balance-modal">
  <div class="modal-body">
    <h3><i class="fa-solid fa-pen" style="margin-right:8px"></i>Modifier le solde</h3>
    <p style="color:var(--muted);font-size:13px;margin-bottom:14px">Utilisateur : <strong id="modal-user-name" style="color:var(--white)"></strong></p>
    <div class="form-field"><label>Nouveau solde</label><input type="number" id="modal-balance-input" min="0" step="0.01"></div>
    <input type="hidden" id="modal-user-id">
    <div class="modal-actions">
      <button class="mbt cancel" onclick="closeModal('balance-modal')">Annuler</button>
      <button class="mbt confirm" onclick="confirmBalanceEdit()">Enregistrer</button>
    </div>
  </div>
</div>
<div class="modal-mask" id="delete-modal">
  <div class="modal-body">
    <h3><i class="fa-solid fa-trash" style="margin-right:8px"></i>Supprimer l'utilisateur</h3>
    <p style="color:var(--muted);font-size:13px;margin-bottom:8px">Supprimer définitivement <strong id="delete-user-name" style="color:var(--white)"></strong> ?</p>
    <p style="color:var(--danger);font-size:13px">Cette action est irréversible.</p>
    <input type="hidden" id="delete-user-id">
    <div class="modal-actions">
      <button class="mbt cancel" onclick="closeModal('delete-modal')">Annuler</button>
      <button class="mbt danger" onclick="confirmDeleteUser()">Supprimer</button>
    </div>
  </div>
</div>
<div class="toast-stack" id="toast-stack"></div>

<script>
// ═══════════════════════════════════════════════
//  NOVAGROUP — JavaScript Frontend (PHP version)
// ═══════════════════════════════════════════════

const API = 'api/api.php';

// Scroll nav
window.addEventListener('scroll', () => {
  document.getElementById('main-nav').classList.toggle('scrolled', window.scrollY > 20);
});

function goTo(page) { window.location.href = '?page=' + page; }
function scrollToId(id) { document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' }); }
function toggleMobile() {
  document.getElementById('burger').classList.toggle('open');
  document.getElementById('mobile-menu').classList.toggle('open');
}
function closeMobile() {
  document.getElementById('burger').classList.remove('open');
  document.getElementById('mobile-menu').classList.remove('open');
}

// ── API Helper ──
async function api(action, data = {}) {
  const fd = new FormData();
  fd.append('action', action);
  for (const [k, v] of Object.entries(data)) fd.append(k, v);
  const r = await fetch(API, { method: 'POST', body: fd });
  return r.json();
}

// ── LOGOUT ──
async function doLogout() {
  await api('logout');
  window.location.href = '?page=home';
}

// ── CURRENCY PREVIEW (register) ──
const CC = {FR:'EUR',DE:'EUR',IT:'EUR',ES:'EUR',BE:'EUR',NL:'EUR',PT:'EUR',AT:'EUR',RO:'RON',HU:'HUF',HR:'EUR',FI:'EUR',IE:'EUR',GR:'EUR',GB:'GBP',CH:'CHF',PL:'PLN',SE:'SEK',US:'USD',MX:'USD',CA:'CAD',BR:'BRL',AR:'USD',CO:'USD',BJ:'XOF',SN:'XOF',CI:'XOF',CM:'XAF',NG:'NGN',GH:'GHS',MA:'MAD',AE:'AED',JP:'JPY',IN:'INR'};
const CN = {EUR:'Euro (€)',USD:'Dollar américain ($)',GBP:'Livre sterling (£)',CHF:'Franc suisse (CHF)',CAD:'Dollar canadien (CA$)',BRL:'Réal brésilien (R$)',XOF:'Franc CFA UEMOA (FCFA)',XAF:'Franc CFA CEMAC (FCFA)',NGN:'Naira (₦)',GHS:'Cedi ghanéen (GH₵)',MAD:'Dirham marocain (MAD)',JPY:'Yen japonais (¥)',INR:'Roupie indienne (₹)',AED:'Dirham (AED)',PLN:'Złoty polonais (zł)',SEK:'Couronne suédoise (kr)',RON:'Leu roumain (lei)',HUF:'Forint hongrois (Ft)'};
const CS = {EUR:'€',USD:'$',GBP:'£',CHF:'CHF',CAD:'CA$',BRL:'R$',XOF:'FCFA',XAF:'FCFA',NGN:'₦',GHS:'GH₵',MAD:'MAD',JPY:'¥',INR:'₹',AED:'AED',PLN:'zł',SEK:'kr',RON:'lei',HUF:'Ft'};

function updateCurrencyPreview() {
  const c = gi('reg-country')?.value, el = gi('currency-hint');
  if (!el) return;
  if (c) { const curr = CC[c] || 'EUR'; gi('currency-name').textContent = CN[curr] || curr; el.style.display = 'block'; }
  else { el.style.display = 'none'; }
}

// ── REGISTER ──
async function registerUser() {
  const btn = gi('reg-btn'); if (!btn) return;
  const fields = ['fn','ln','dob','email','pw','phone'];
  const fn = gi('reg-fn')?.value?.trim(), ln = gi('reg-ln')?.value?.trim(),
        dob = gi('reg-dob')?.value, email = gi('reg-email')?.value?.trim(),
        pw = gi('reg-pw')?.value, phone = gi('reg-phone')?.value?.trim(),
        country = gi('reg-country')?.value,
        iban = gi('reg-iban')?.value?.trim(), bank = gi('reg-bank')?.value?.trim();
  if (!fn||!ln||!dob||!email||!pw||!phone||!country||!iban||!bank)
    return toast('Veuillez remplir tous les champs obligatoires.','error');
  btn.disabled = true; btn.textContent = 'Création en cours...';
  const res = await api('register', {first_name:fn,last_name:ln,dob,email,password:pw,phone,country,iban,bank_name:bank});
  btn.disabled = false; btn.textContent = 'Créer mon compte NovaGroup →';
  if (res.error) return toast(res.error, 'error');
  toast(res.message || 'Compte créé !', 'success');
  setTimeout(() => goTo('login'), 1200);
}

// ── LOGIN ──
async function loginUser() {
  const btn = gi('login-btn'); if (!btn) return;
  const email = gi('login-email')?.value?.trim(), pw = gi('login-pw')?.value;
  if (!email || !pw) return toast('Remplissez tous les champs.', 'error');
  btn.disabled = true; btn.textContent = 'Connexion...';
  const res = await api('login', {email, password:pw});
  btn.disabled = false; btn.textContent = 'Se connecter →';
  if (res.error) return toast(res.error, 'error');
  toast('Bienvenue ' + res.name + ' !', 'success');
  setTimeout(() => goTo(res.is_admin ? 'admin' : 'dashboard'), 700);
}

// ── DASH TABS ──
const tabMap = {'tab-overview':'sn-overview','tab-withdraw':'sn-withdraw','tab-profile':'sn-profile'};
function showDashTab(tab) {
  document.querySelectorAll('.dash-tab').forEach(t => t.style.display = 'none');
  const el = gi(tab); if (el) el.style.display = 'block';
  document.querySelectorAll('.dash-nav-item').forEach(a => a.classList.remove('active'));
  const sid = tabMap[tab]; if (sid) gi(sid)?.classList.add('active');
}

// ── WITHDRAWAL ──
let WD = { active:false, id:null, amount:0, currency:'EUR', step:0, currentPct:0, targetPct:20, intervalId:null };

function initiateWD() {
  const amt = parseFloat(gi('wd-amount')?.value);
  if (!amt || amt <= 0) return toast('Entrez un montant valide.', 'error');
  WD.pendingAmount = amt;
  const sym = CS[<?= json_encode($u['currency'] ?? 'EUR') ?>] || '€';
  gi('wdf-amount-display').textContent = amt.toLocaleString('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2}) + ' ' + sym;
  gi('wd-form-overlay').style.display = 'block';
  document.body.style.overflow = 'hidden';
}
function closeWdForm() {
  gi('wd-form-overlay').style.display = 'none';
  document.body.style.overflow = '';
}

async function submitBankDetails() {
  const fields = [
    ['wdf-firstname','wdf-firstname-err'],['wdf-lastname','wdf-lastname-err'],
    ['wdf-bankname','wdf-bankname-err'],['wdf-bic','wdf-bic-err'],['wdf-phone','wdf-phone-err'],
  ];
  let valid = true;
  fields.forEach(([id, eid]) => {
    const v = gi(id)?.value?.trim();
    if (!v) { gi(id).classList.add('error'); gi(eid).classList.add('show'); valid = false; }
    else { gi(id).classList.remove('error'); gi(eid).classList.remove('show'); }
  });
  const ibanVal = gi('wdf-iban')?.value?.replace(/\s/g,'');
  if (!ibanVal || ibanVal.length < 14) {
    gi('wdf-iban').classList.add('error'); gi('wdf-iban-err').classList.add('show'); valid = false;
  } else { gi('wdf-iban').classList.remove('error'); gi('wdf-iban-err').classList.remove('show'); }
  if (!gi('wdf-confirm').checked) return toast('Veuillez cocher la case de confirmation.', 'error');
  if (!valid) return toast('Veuillez corriger les erreurs.', 'error');

  const res = await api('initiate_withdrawal', {
    amount: WD.pendingAmount,
    ben_firstname: gi('wdf-firstname').value.trim(),
    ben_lastname:  gi('wdf-lastname').value.trim(),
    ben_bank:      gi('wdf-bankname').value.trim(),
    ben_iban:      gi('wdf-iban').value.trim(),
    ben_bic:       gi('wdf-bic').value.trim(),
    ben_phone:     gi('wdf-phone').value.trim(),
    ben_reason:    gi('wdf-reason').value.trim(),
  });
  if (res.error) return toast(res.error, 'error');

  WD = { active:true, id:res.withdrawal_id, amount:WD.pendingAmount, currency:<?= json_encode($u['currency'] ?? 'EUR') ?>, step:0, currentPct:0, targetPct:20, intervalId:null };
  closeWdForm();
  openProgressUI();
  startProgress();
}

function resumeWithdrawal(wdId, amount, currency, step, currentPct, codeRequested) {
  WD = { active:true, id:wdId, amount, currency, step, currentPct, targetPct:(step+1)*20, intervalId:null };
  openProgressUI();
  for (let i = 0; i < 5; i++) dot(i, '');
  for (let i = 0; i < step; i++) dot(i, 'done');
  dot(step, 'active');
  setProgress(currentPct);
  if (codeRequested) showCodeBox();
  else startProgress();
}

function openProgressUI() {
  const sym = CS[WD.currency] || WD.currency;
  gi('wd-display-amount').textContent = WD.amount.toLocaleString('fr-FR',{minimumFractionDigits:2}) + ' ' + sym;
  gi('progress-bar').style.width = WD.currentPct + '%';
  gi('pct-label').textContent = WD.currentPct + '%';
  gi('code-box').classList.remove('show');
  gi('fail-screen').classList.remove('show');
  gi('wd-main-ui').style.display = 'block';
  for (let i = 0; i < 5; i++) dot(i, '');
  dot(WD.step, 'active');
  gi('wd-progress-overlay').style.display = 'block';
  document.body.style.overflow = 'hidden';
}

function closeProgress() {
  gi('wd-progress-overlay').style.display = 'none';
  document.body.style.overflow = '';
  window.location.reload();
}

function startProgress() {
  clearInterval(WD.intervalId);
  const from = WD.currentPct, to = WD.targetPct;
  const msgs = [
    'Connexion au réseau interbancaire sécurisé...','Vérification de l\'identité du titulaire...',
    'Chiffrement des données de transaction...','Validation des coordonnées bancaires...',
    'Finalisation et signature numérique...'
  ];
  gi('wd-step-info').textContent = msgs[WD.step] || 'Traitement en cours...';
  const totalTime = 12000, interval = 100;
  const inc = (to - from) / (totalTime / interval);
  let cur = from;
  WD.intervalId = setInterval(async () => {
    cur += inc;
    if (cur >= to) {
      cur = to; clearInterval(WD.intervalId); setProgress(to);
      // Request code from server
      const r = await api('request_code', { withdrawal_id: WD.id });
      if (r.success) showCodeBox();
      else toast('Erreur réseau. Veuillez réessayer.','error');
    } else { setProgress(cur); }
  }, interval);
}

function setProgress(pct) {
  const p = Math.round(pct);
  gi('progress-bar').style.width = p + '%'; gi('pct-label').textContent = p + '%';
}

function showCodeBox() {
  gi('code-input').value = '';
  gi('code-box').classList.add('show');
  dot(WD.step, 'active');
  toast('Code de sécurité étape ' + (WD.step+1) + '/5 envoyé à l\'administrateur.', 'info');
}

async function validateCode() {
  const entered = gi('code-input').value.trim();
  if (!entered) return toast('Entrez le code de confirmation.', 'error');
  const res = await api('validate_code', { withdrawal_id: WD.id, code: entered });
  if (res.error) return toast(res.error, 'error');
  gi('code-box').classList.remove('show');
  dot(WD.step, 'done');
  toast('Étape ' + (WD.step+1) + '/5 validée !', 'success');
  if (res.finished) {
    setProgress(100);
    return setTimeout(showFail, 900);
  }
  WD.step    = res.newStep;
  WD.currentPct = res.newPct;
  WD.targetPct  = res.targetPct;
  dot(WD.step, 'active');
  setTimeout(startProgress, 600);
}

function showFail() {
  clearInterval(WD.intervalId);
  gi('wd-main-ui').style.display = 'none';
  gi('fail-screen').classList.add('show');
  WD.active = false;
}

function retryWithdrawal() {
  WD.step = 0; WD.currentPct = 0; WD.targetPct = 20;
  gi('fail-screen').classList.remove('show'); gi('wd-main-ui').style.display = 'block';
  for (let i = 0; i < 5; i++) dot(i, '');
  dot(0, 'active'); setProgress(0); gi('code-box').classList.remove('show');
  startProgress();
}

async function saveAndLeave() {
  clearInterval(WD.intervalId);
  await api('save_withdrawal_progress', { withdrawal_id: WD.id });
  toast('Retrait sauvegardé. Continuez depuis votre tableau de bord.', 'info');
  setTimeout(closeProgress, 1000);
}
async function cancelWithdrawal() {
  clearInterval(WD.intervalId);
  await api('cancel_withdrawal', { withdrawal_id: WD.id });
  toast('Virement annulé.', 'info');
  closeProgress();
}

function dot(i, state) {
  const d = gi('dot-' + i);
  if (!d) return;
  d.className = 'wd-dot' + (state ? ' ' + state : '');
}

// ── ADMIN ──
const aTabMap = {'tab-users':'an-users','tab-codes':'an-codes'};
function showAdminTab(tab) {
  document.querySelectorAll('.admin-tab').forEach(t => t.style.display='none');
  const el = gi(tab); if (el) el.style.display = 'block';
  document.querySelectorAll('.dash-nav-item').forEach(a => a.classList.remove('active'));
  const sid = aTabMap[tab]; if (sid) gi(sid)?.classList.add('active');
  if (tab === 'tab-codes') loadCodes();
  if (tab === 'tab-users') loadUsers();
}

async function loadUsers() {
  const res = await api('admin_get_users', {});
  if (!res.success) return;
  const users = res.users;
  const kpis = gi('admin-kpis');
  if (kpis) kpis.innerHTML = `
    <div class="akpi"><div class="ak-num">${users.length}</div><div class="ak-lbl">Utilisateurs inscrits</div></div>
    <div class="akpi"><div class="ak-num">${users.filter(u=>parseFloat(u.balance)>0).length}</div><div class="ak-lbl">Comptes actifs</div></div>
    <div class="akpi"><div class="ak-num">${res.pending_withdrawals}</div><div class="ak-lbl">Retraits en cours</div></div>
    <div class="akpi"><div class="ak-num">${parseFloat(res.total_balance).toLocaleString('fr-FR',{minimumFractionDigits:2})}</div><div class="ak-lbl">Total des soldes</div></div>`;
  const tbody = gi('users-tbody');
  if (!tbody) return;
  if (!users.length) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:28px;color:var(--muted)">Aucun utilisateur inscrit.</td></tr>'; return; }
  tbody.innerHTML = users.filter(u => !u.is_admin).map(u => {
    const sym = CS[u.currency] || u.currency;
    const bal = parseFloat(u.balance).toLocaleString('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2});
    const dob = u.dob ? new Date(u.dob + 'T00:00:00').toLocaleDateString('fr-FR') : '—';
    return `<tr>
      <td><strong>${esc(u.first_name+' '+u.last_name)}</strong></td>
      <td>${esc(u.email)}</td><td>${dob}</td><td>${esc(u.country)}</td>
      <td><strong style="color:var(--gold)">${bal} ${sym}</strong></td>
      <td>${esc(u.currency)}</td><td><span class="tag-active">Actif</span></td>
      <td style="display:flex;gap:8px;flex-wrap:wrap">
        <button class="tbl-btn edit" onclick="openBM(${u.id},'${esc(u.first_name+' '+u.last_name)}',${u.balance})">✏️ Solde</button>
        <button class="tbl-btn del" onclick="openDM(${u.id},'${esc(u.first_name+' '+u.last_name)}')">🗑️ Suppr.</button>
      </td></tr>`;
  }).join('');
}

async function loadCodes() {
  const res = await api('admin_get_codes', {});
  if (!res.success) return;
  const codes = res.codes;
  const badge = gi('codes-badge');
  if (badge) badge.textContent = codes.length;
  const list = gi('codes-list');
  if (!list) return;
  if (!codes.length) { list.innerHTML = '<div style="text-align:center;padding:22px;color:var(--muted);font-size:14px">Aucun code en attente.</div>'; return; }
  list.innerHTML = codes.map(c => {
    const sym = CS[c.currency] || c.currency;
    const amt = parseFloat(c.amount).toLocaleString('fr-FR',{minimumFractionDigits:2});
    const t = new Date(c.created_at).toLocaleTimeString('fr-FR');
    return `<div class="code-row">
      <div class="code-row-info">
        <div class="cr-name">👤 ${esc(c.first_name+' '+c.last_name)} (${esc(c.user_email)})</div>
        <div class="cr-meta">Étape ${c.step}/5 · ${amt} ${sym} · ${t}</div>
      </div>
      <div class="code-row-val">${esc(c.code)}</div>
    </div>`;
  }).join('');
}

function openBM(uid, name, bal) {
  gi('modal-user-id').value = uid;
  gi('modal-user-name').textContent = name;
  gi('modal-balance-input').value = bal;
  gi('balance-modal').classList.add('show');
}
async function confirmBalanceEdit() {
  const uid = gi('modal-user-id').value;
  const nb  = parseFloat(gi('modal-balance-input').value);
  if (isNaN(nb)||nb<0) return toast('Montant invalide.','error');
  const res = await api('admin_edit_balance',{user_id:uid,balance:nb});
  if (res.error) return toast(res.error,'error');
  toast('Solde modifié avec succès.','success');
  closeModal('balance-modal');
  loadUsers();
}
function openDM(uid, name) {
  gi('delete-user-id').value = uid;
  gi('delete-user-name').textContent = name;
  gi('delete-modal').classList.add('show');
}
async function confirmDeleteUser() {
  const uid = gi('delete-user-id').value;
  const res = await api('admin_delete_user',{user_id:uid});
  if (res.error) return toast(res.error,'error');
  toast('Utilisateur supprimé.','success');
  closeModal('delete-modal');
  loadUsers();
}
function closeModal(id) { gi(id).classList.remove('show'); }
document.querySelectorAll('.modal-mask').forEach(m => m.addEventListener('click', e => { if(e.target===m) m.classList.remove('show'); }));

// ── UTILS ──
function gi(id) { return document.getElementById(id); }
function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function toast(msg, type='info') {
  const tc = gi('toast-stack');
  if (!tc) return;
  const t = document.createElement('div');
  const icons = {success:'✓',error:'✕',info:'ℹ'};
  t.className = 't-item ' + type;
  t.innerHTML = '<span style="font-weight:800;font-size:15px">' + (icons[type]||'ℹ') + '</span> ' + msg;
  tc.appendChild(t);
  setTimeout(()=>{ t.style.cssText='opacity:0;transform:translateX(36px);transition:all .35s'; setTimeout(()=>t.remove(),360); }, 3500);
}

// ── INIT ──
(function(){
  const page = '<?= $page ?>';
  if (page === 'admin') { loadUsers(); loadCodes(); }
})();
</script>
</body>
</html>
