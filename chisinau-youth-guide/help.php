<?php require_once 'config/config.php'; ?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajutor - <?php echo SITE_NAME; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-map-marked-alt"></i>
                Ghidul Tinerilor
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Acasă</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">Categorii</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="places.php">Locuri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="discussions.php">Discuții</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <button class="theme-toggle me-3" onclick="toggleTheme()" title="Schimbă tema">
                            <i class="fas fa-moon" id="theme-icon"></i>
                        </button>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-user"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/logout.php">Ieșire</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">Conectare</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary ms-2" href="auth/register.php">Înregistrare</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="py-5 mt-5 bg-gradient text-white">
        <div class="container">
            <div class="text-center">
                <h1 class="display-4 fw-bold mb-3">Centrul de Ajutor</h1>
                <p class="lead">Găsește răspunsuri la întrebările tale despre Ghidul Tinerilor</p>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h2 class="mb-4">Întrebări Frecvente</h2>
                    
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Cum îmi creez un cont?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Pentru a-ți crea un cont, apasă pe butonul "Înregistrare" din partea de sus a paginii. 
                                    Completează formularul cu informațiile tale și vei primi acces la toate funcționalitățile platformei.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Cum sugerez un loc nou?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    După ce te-ai conectat, poți sugera un loc nou accesând secțiunea "Sugerează Loc" din dashboard-ul tău 
                                    sau din meniul principal. Completează formularul cu detaliile locului și echipa noastră îl va revizui.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Cum salvez locuri favorite?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Pentru a salva un loc ca favorit, trebuie să fii conectat. Apoi, apasă pe iconița de inimă 
                                    de pe cardul locului. Locurile favorite le poți vedea în dashboard-ul tău.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Cum caut locuri specifice?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Poți căuta locuri folosind bara de căutare de pe pagina principală sau din secțiunea "Locuri". 
                                    De asemenea, poți filtra după categorii pentru a găsi mai ușor ce cauți.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    Ce fac dacă găsesc informații greșite?
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Dacă găsești informații incorecte despre un loc, te rugăm să ne contactezi prin formularul de contact. 
                                    Vom verifica și corecta informațiile cât mai repede posibil.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-headset text-primary me-2"></i>
                                Ai nevoie de ajutor suplimentar?
                            </h5>
                            <p class="card-text">Nu ai găsit răspunsul la întrebarea ta? Contactează-ne direct!</p>
                            <a href="contact.php" class="btn btn-primary">
                                <i class="fas fa-envelope"></i> Contactează-ne
                            </a>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-rocket text-success me-2"></i>
                                Începe să explorezi
                            </h5>
                            <div class="d-grid gap-2">
                                <a href="places.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-search"></i> Caută Locuri
                                </a>
                                <a href="categories.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-list"></i> Vezi Categorii
                                </a>
                                <?php if (!isLoggedIn()): ?>
                                    <a href="auth/register.php" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-user-plus"></i> Creează Cont
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="brand-title text-white">
                        <i class="fas fa-map-marked-alt"></i>
                        Ghidul Tinerilor Chișinău
                    </h5>
                    <p class="text-muted">Platforma ta pentru a descoperi și explora cele mai bune oportunități din Chișinău.</p>
                </div>
                
                <div class="col-md-3">
                    <h6>Link-uri Utile</h6>
                    <ul class="list-unstyled">
                        <li><a href="categories.php" class="text-muted">Categorii</a></li>
                        <li><a href="places.php" class="text-muted">Locuri</a></li>
                        <li><a href="discussions.php" class="text-muted">Discuții</a></li>
                        <li><a href="contact.php" class="text-muted">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h6>Suport</h6>
                    <ul class="list-unstyled">
                        <li><a href="help.php" class="text-muted">Ajutor</a></li>
                        <li><a href="privacy.php" class="text-muted">Confidențialitate</a></li>
                        <li><a href="terms.php" class="text-muted">Termeni</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted">&copy; 2024 Ghidul Tinerilor Chișinău. Toate drepturile rezervate.</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="social-links">
                        <a href="#" class="text-muted me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-telegram"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
