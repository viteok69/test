<?php require_once 'config/config.php'; ?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termeni și Condiții - <?php echo SITE_NAME; ?></title>
    
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
    <section class="py-5 mt-5 bg-light">
        <div class="container">
            <div class="text-center">
                <h1 class="display-5 fw-bold mb-3">Termeni și Condiții</h1>
                <p class="lead text-muted">Ultima actualizare: Decembrie 2024</p>
            </div>
        </div>
    </section>

    <!-- Content -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body p-5">
                            <h3>1. Acceptarea Termenilor</h3>
                            <p>Prin utilizarea platformei Ghidul Tinerilor Chișinău, acceptați să respectați acești termeni și condiții. Dacă nu sunteți de acord cu acești termeni, vă rugăm să nu utilizați serviciile noastre.</p>

                            <h3>2. Descrierea Serviciului</h3>
                            <p>Ghidul Tinerilor Chișinău este o platformă online care oferă informații despre locuri, evenimente și oportunități pentru tinerii din Chișinău, Moldova.</p>

                            <h3>3. Conturile de Utilizator</h3>
                            <p>Pentru a accesa anumite funcționalități, trebuie să vă creați un cont. Sunteți responsabili pentru:</p>
                            <ul>
                                <li>Menținerea confidențialității parolei</li>
                                <li>Toate activitățile care au loc sub contul dumneavoastră</li>
                                <li>Notificarea imediată în cazul utilizării neautorizate a contului</li>
                            </ul>

                            <h3>4. Conținutul Utilizatorilor</h3>
                            <p>Utilizatorii pot contribui cu conținut prin sugestii de locuri, comentarii și recenzii. Prin trimiterea conținutului, garantați că:</p>
                            <ul>
                                <li>Dețineți drepturile asupra conținutului</li>
                                <li>Conținutul nu încalcă drepturile terților</li>
                                <li>Conținutul este adevărat și nu este înșelător</li>
                                <li>Conținutul nu este ofensator sau ilegal</li>
                            </ul>

                            <h3>5. Conduita Utilizatorilor</h3>
                            <p>Utilizatorii se angajează să nu:</p>
                            <ul>
                                <li>Publice conținut fals, înșelător sau spam</li>
                                <li>Hărțuiască sau amenințe alți utilizatori</li>
                                <li>Încalce legile aplicabile</li>
                                <li>Interfereze cu funcționarea platformei</li>
                                <li>Creeze conturi false sau multiple</li>
                            </ul>

                            <h3>6. Proprietatea Intelectuală</h3>
                            <p>Toate drepturile de proprietate intelectuală asupra platformei aparțin Ghidul Tinerilor Chișinău. Utilizatorii primesc o licență limitată pentru utilizarea personală a serviciilor.</p>

                            <h3>7. Limitarea Răspunderii</h3>
                            <p>Platforma este furnizată "ca atare". Nu garantăm acuratețea informațiilor furnizate de utilizatori și nu suntem responsabili pentru:</p>
                            <ul>
                                <li>Daunele rezultate din utilizarea platformei</li>
                                <li>Conținutul furnizat de terțe părți</li>
                                <li>Întreruperile serviciului</li>
                            </ul>

                            <h3>8. Modificarea Termenilor</h3>
                            <p>Ne rezervăm dreptul de a modifica acești termeni în orice moment. Utilizatorii vor fi notificați despre modificările importante prin email sau prin notificări pe platformă.</p>

                            <h3>9. Încetarea Serviciului</h3>
                            <p>Putem suspenda sau închide conturile utilizatorilor care încalcă acești termeni. Utilizatorii pot închide conturile lor în orice moment.</p>

                            <h3>10. Legea Aplicabilă</h3>
                            <p>Acești termeni sunt guvernați de legile Republicii Moldova. Orice dispute vor fi rezolvate în instanțele competente din Chișinău.</p>

                            <h3>11. Contact</h3>
                            <p>Pentru întrebări despre acești termeni, ne puteți contacta la:</p>
                            <p><strong>Email:</strong> legal@ghidultinerilor.md</p>

                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Notă:</strong> Acești termeni și condiții sunt un document demonstrativ pentru proiectul Ghidul Tinerilor Chișinău.
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
