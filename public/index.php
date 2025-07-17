<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Cabinet des Ardentes</title>

    <link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet" />

    <link href="assets/css/home.css" rel="stylesheet" />
    <script src="js/login/menuManager.js"></script>
    <link rel="stylesheet" href="assets/css/popup.css">
</head>

<body id="page-top">
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand" href="#page-top">Cabinet des Ardentes</a>
            <button class="navbar-toggler navbar-toggler-right" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false"
                aria-label="Toggle navigation">
                Menu
                <i class="fas fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" id='connexion' href="login.php">Connexion</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">Notre Engagement</a></li>
                    <li class="nav-item"><a class="nav-link" href="#projects">Notre Equipe</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Masthead-->
    <header class="masthead">
        <div class="container px-4 px-lg-5 d-flex h-100 align-items-center justify-content-center">
            <div class="d-flex justify-content-center">
                <div class="text-center">
                    <h1 class="mx-auto my-0 text-uppercase">Cabinet des Ardentes</h1>
                    <h2 class="text-white-50 mx-auto mt-2 mb-5">Centre médical pluridisciplinaire.</h2>
                    <a class="btn btn-primary" aria-label="Prendre rendez-vous au cabinet Les Ardentes" href="patient-dashboard.php">Prendre Rendez-Vous</a>
                </div>
            </div>
        </div>
    </header>
    <!-- About-->
    <section class="about-section text-center" id="about">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5 justify-content-center">
                <div class="col-lg-8">
                    <h2 class="text-white mb-4">Notre engagement pour votre santé</h2>
                    <p class="text-white-50">
                        Le Cabinet des Ardentes est un centre médical pluridisciplinaire situé à Liège. Notre équipe de
                        professionnels de santé vous accueille dans un environnement moderne, bienveillant et à taille
                        humaine.
                        <br><br>
                        Nous plaçons le patient au cœur de nos priorités, en assurant un suivi personnalisé, une écoute
                        attentive et des soins adaptés à chaque besoin.
                    </p>
                </div>
            </div>
            <img class="img-fluid" width="300px" src="assets/img/logoText.png" alt="Équipe médicale du cabinet" />
        </div>
    </section>

    <!-- Projects -->
    <section class="projects-section bg-light" id="projects">
        <div class="container px-4 px-lg-5">
            <h2 class="text-center mb-5">Notre Équipe Médicale</h2>

            <div class="row gx-0 mb-5 mb-lg-0 justify-content-center">
                <div class="col-lg-6"><img class="img-fluid" src="assets/img/martin.jpg" alt="Dr Élise Martin" /></div>
                <div class="col-lg-6">
                    <div class="bg-black text-center h-100 project">
                        <div class="d-flex h-100">
                            <div class="project-text w-100 my-auto text-center text-lg-left">
                                <h4 class="text-white">Dr Élise Martin</h4>
                                <p class="mb-0 text-white-50">
                                    Médecin généraliste depuis plus de 15 ans, le Dr Martin vous propose un suivi de
                                    proximité, centré sur la prévention, l'écoute et la prise en charge globale des
                                    patients de tout âge.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row gx-0 justify-content-center">
                <div class="col-lg-6"><img class="img-fluid" src="assets/img/hakim.jpg" alt="Dr Hakim Bensaïd" /></div>
                <div class="col-lg-6 order-lg-first">
                    <div class="bg-black text-center h-100 project">
                        <div class="d-flex h-100">
                            <div class="project-text w-100 my-auto text-center text-lg-right">
                                <h4 class="text-white">Dr Hakim Bensaïd</h4>
                                <p class="mb-0 text-white-50">
                                    Spécialiste en dermatologie, le Dr Bensaïd traite les affections de la peau, des
                                    cheveux et des ongles. Il propose également des actes de dermatologie esthétique et
                                    laser.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!-- Contact-->
    <section class="contact-section bg-black" id="contact">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card py-4 h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-map-marked-alt text-primary mb-2"></i>
                            <h4 class="text-uppercase m-0">Adresse</h4>
                            <hr class="my-4 mx-auto" />
                            <div class="small text-black-50">2 rue des Pâquerettes, 4000 Liège</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card py-4 h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-envelope text-primary mb-2"></i>
                            <h4 class="text-uppercase m-0">Email</h4>
                            <hr class="my-4 mx-auto" />
                            <div class="small text-black-50">
                                <a href="mailto:contact@cabinet-ardentes.be">contact@cabinet-ardentes.be</a>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card py-4 h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-mobile-alt text-primary mb-2"></i>
                            <h4 class="text-uppercase m-0">Téléphone</h4>
                            <hr class="my-4 mx-auto" />
                            <div class="small text-black-50">+32 4 123 45 67</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    
    <footer class="footer bg-black small text-center text-white-50">
        <div class="container px-4 px-lg-5"><a href="legal-notice.php">Mentions Légales</a></div>
        <br>
        <p style='color: white' ;>&copy; 2025 Cabinet des Ardentes. Tous droits réservés. — <span class="fst-italic">Copyright by dembean</span></p>
    </footer>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>