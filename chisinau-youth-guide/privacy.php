<?php require_once 'config/config.php'; ?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politica de Confidențialitate - <?php echo SITE_NAME; ?></title>
    
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
                <h1 class="display-5 fw-bold mb-3">Politica de Confidențialitate</h1>
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
                            <h3>1. Introducere</h3>
                            <p>Ghidul Tinerilor Chișinău respectă confidențialitatea utilizatorilor săi și se angajează să protejeze informațiile personale pe care le colectează și le procesează.</p>

                            <h3>2. Informațiile pe care le colectăm</h3>
                            <p>Colectăm următoarele tipuri de informații:</p>
                            <ul>
                                <li><strong>Informații de cont:</strong> nume, prenume, email, vârsta, nume de utilizator</li>
                                <li><strong>Informații de profil:</strong> biografia, preferințele</li>
                                <li><strong>Activitatea pe site:</strong> locurile favorite, sugestiile trimise</li>
                                <li><strong>Informații tehnice:</strong> adresa IP, tipul de browser, sistemul de operare</li>
                            </ul>

                            <h3>3. Cum folosim informațiile</h3>
                            <p>Folosim informațiile colectate pentru:</p>
                            <ul>
                                <li>Furnizarea serviciilor platformei</li>
                                <li>Personalizarea experienței utilizatorului</li>
                                <li>Comunicarea cu utilizatorii</li>
                                <li>Îmbunătățirea serviciilor noastre</li>
                                <li>Respectarea obligațiilor legale</li>
                            </ul>

                            <h3>4. Partajarea informațiilor</h3>
                            <p>Nu vindem, nu închiriem și nu partajăm informațiile personale cu terțe părți, cu excepția următoarelor situații:</p>
                            <ul>
                                <li>Cu consimțământul explicit al utilizatorului</li>
                                <li>Pentru respectarea obligațiilor legale</li>
                                <li>Pentru protejarea drepturilor și siguranței noastre și a utilizatorilor</li>
                            </ul>

                            <h3>5. Securitatea datelor</h3>
                            <p>Implementăm măsuri de securitate tehnice și organizatorice pentru a proteja informațiile personale împotriva accesului neautorizat, modificării, divulgării sau distrugerii.</p>

                            <h3>6. Drepturile utilizatorilor</h3>
                            <p>Utilizatorii au următoarele drepturi:</p>
                            <ul>
                                <li>Dreptul de acces la datele personale</li>
                                <li>Dreptul de rectificare a datelor incorecte</li>
                                <li>Dreptul de ștergere a datelor</li>
                                <li>Dreptul de portabilitate a datelor</li>
                                <li>Dreptul de opoziție la procesarea datelor</li>
                            </ul>

                            <h3>7. Cookies</h3>
                            <p>Folosim cookies pentru a îmbunătăți experiența utilizatorului și pentru a analiza traficul pe site. Utilizatorii pot configura browser-ul pentru a refuza cookies-urile.</p>

                            <h3>8. Modificări ale politicii</h3>
                            <p>Ne rezervăm dreptul de a modifica această politică de confidențialitate. Utilizatorii vor fi notificați despre modificările importante.</p>

                            <h3>9. Contact</h3>
                            <p>Pentru întrebări despre această politică de confidențialitate, ne puteți contacta la:</p>
                            <p><strong>Email:</strong> privacy@ghidultinerilor.md</p>

                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Notă:</strong> Această politică de confidențialitate este un document demonstrativ pentru proiectul Ghidul Tinerilor Chișinău.
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
