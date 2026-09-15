<?php

namespace Database\Seeders;

use App\Enums\QuestionType;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Option;
use App\Models\Promotion;
use App\Models\User;
use Database\Seeders\Concerns\BuildsChapters;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    use BuildsChapters;

    public function run(): void
    {
        $sciences = $this->faculty('faculte-des-sciences', 'Faculté des Sciences', 'FDS');
        $economie = $this->faculty('faculte-economie', 'Faculté d\'Économie et de Gestion', 'FEG');
        $droit = $this->faculty('faculte-de-droit', 'Faculté de Droit', 'FDD');

        $informatique = $this->option($sciences, 'informatique', 'Informatique');
        $mathematiques = $this->option($sciences, 'mathematiques', 'Mathématiques');
        $gestion = $this->option($economie, 'gestion-financiere', 'Gestion financière');
        $economieOption = $this->option($economie, 'economie', 'Économie');
        $droitPrive = $this->option($droit, 'droit-prive', 'Droit privé');

        $l1 = $this->promotion('l1', 'Licence 1', 1);
        $l2 = $this->promotion('l2', 'Licence 2', 2);
        $l3 = $this->promotion('l3', 'Licence 3', 3);

        $this->seedInformatique($sciences, $informatique, $l1, $l2, $l3);
        $this->seedMathematiques($sciences, $mathematiques, $l1);
        $this->seedGestion($economie, $gestion, $l1, $l2);
        $this->seedEconomie($economie, $economieOption, $l1);
        $this->seedDroit($droit, $droitPrive, $l1);

        $this->seedUsers(
            sciences: $sciences,
            economie: $economie,
            droit: $droit,
            informatique: $informatique,
            mathematiques: $mathematiques,
            gestion: $gestion,
            economieOption: $economieOption,
            droitPrive: $droitPrive,
            l1: $l1,
            l2: $l2,
            l3: $l3,
        );
    }

    private function seedInformatique(Faculty $faculty, Option $option, Promotion $l1, Promotion $l2, Promotion $l3): void
    {
        $algo = $this->course($faculty, $option, $l1, 'algorithmique-1', 'Algorithmique et programmation',
            'Cours fondateur de la licence Informatique : conception d\'algorithmes, structures de contrôle, complexité et premières bonnes pratiques de programmation.');

        $this->chapterWithQuiz($algo, 1, 'De l\'algorithme au programme', <<<'TXT'
Un algorithme est une suite finie d'instructions non ambiguës, destinée à résoudre un problème. Avant d'écrire une ligne de code, on précise les données d'entrée, le résultat attendu et les étapes de transformation.

Les structures de contrôle essentielles sont la séquence, le choix (si… alors… sinon) et la répétition (pour, tant que). Un algorithme doit se terminer : une boucle infinie n'est pas un algorithme au sens strict.

On distingue souvent trois qualités : la correction (le résultat est celui prévu), l'efficacité (temps et mémoire raisonnables) et la lisibilité (un autre étudiant doit pouvoir relire le raisonnement). Le passage à un langage (Python, Java, C) n'est que la dernière étape : si l'algorithme est faux, le programme le sera aussi.
TXT, [
            'title' => 'Interrogation — Algorithmes',
            'passing_score' => 50,
            'questions' => [
                [
                    'prompt' => 'Un algorithme doit-il nécessairement se terminer ?',
                    'answers' => [
                        ['Oui, une procédure qui ne s\'arrête jamais n\'est pas un algorithme au sens strict.', true],
                        ['Non, une boucle infinie reste un algorithme valide.', false],
                        ['Oui, mais uniquement s\'il est écrit en Python.', false],
                    ],
                ],
                [
                    'prompt' => 'Quelle structure de contrôle exprime un choix entre deux traitements ?',
                    'answers' => [
                        ['La séquence', false],
                        ['Le branchement conditionnel (si… alors… sinon)', true],
                        ['La récursivité', false],
                    ],
                ],
                [
                    'type' => QuestionType::Text,
                    'prompt' => 'Citez un langage de programmation mentionné dans le chapitre (un mot).',
                    'accepted' => ['Python', 'Java', 'C'],
                ],
            ],
        ]);

        $this->chapterWithQuiz($algo, 2, 'Complexité algorithmique', <<<'TXT'
La complexité mesure les ressources consommées par un algorithme, principalement le temps d'exécution et l'espace mémoire, en fonction de la taille n des données.

On s'intéresse surtout au pire des cas, noté avec le grand O (Big O). Par exemple, une recherche linéaire dans un tableau non trié est en O(n) : dans le pire cas, on parcourt tous les éléments. Une recherche dichotomique sur un tableau trié est en O(log n). Un tri par sélection naïf est en O(n²).

La notation Θ (thêta) décrit un encadrement plus précis (pire cas et meilleur cas du même ordre). Ω (oméga) donne une borne inférieure. En licence, retenir O suffit pour comparer deux algorithmes : un algorithme en O(n) est préférable, pour n grand, à un algorithme en O(n²).
TXT, [
            'title' => 'Interrogation — Complexité',
            'passing_score' => 50,
            'questions' => [
                [
                    'prompt' => 'Quelle notation désigne le pire des cas ?',
                    'answers' => [
                        ['Big O (O)', true],
                        ['Theta (Θ)', false],
                        ['Omega (Ω)', false],
                    ],
                ],
                [
                    'prompt' => 'Quelle est la complexité d\'une recherche dichotomique sur un tableau trié ?',
                    'answers' => [
                        ['O(n)', false],
                        ['O(log n)', true],
                        ['O(n²)', false],
                    ],
                ],
                [
                    'type' => QuestionType::Text,
                    'prompt' => 'Comment nomme-t-on la recherche dans un tableau trié présentée dans le chapitre (un mot) ?',
                    'accepted' => ['dichotomique', 'dichotomie', 'binaire'],
                ],
            ],
        ]);

        $this->chapterWithQuiz($algo, 3, 'Tableaux, parcours et invariants', <<<'TXT'
Le tableau est la structure contiguë de base : les éléments sont accessibles en temps constant par leur indice. Un parcours consiste à visiter chaque case exactement une fois, en général de 0 à n-1.

Un invariant de boucle est une propriété vraie avant, pendant et après chaque itération. Il sert à prouver qu'un algorithme est correct. Exemple : dans une recherche du maximum, « max contient le plus grand élément déjà examiné » est un invariant.

Les erreurs fréquentes en L1 sont le dépassement d'indice (accéder à t[n] alors que le dernier indice est n-1) et la confusion entre valeur et indice. Toujours documenter la taille réelle du tableau et tester les cas limites : tableau vide, un seul élément, valeurs toutes égales.
TXT, [
            'title' => 'Interrogation — Tableaux',
            'passing_score' => 50,
            'questions' => [
                [
                    'prompt' => 'Dans un tableau de n éléments indexé à partir de 0, quel est l\'indice du dernier élément ?',
                    'answers' => [
                        ['n', false],
                        ['n-1', true],
                        ['n+1', false],
                    ],
                ],
                [
                    'prompt' => 'Un invariant de boucle est :',
                    'answers' => [
                        ['Une optimisation du compilateur', false],
                        ['Une propriété vraie à chaque itération, utilisée pour prouver la correction', true],
                        ['Un type de donnée immuable', false],
                    ],
                ],
            ],
        ]);

        $bdd = $this->course($faculty, $option, $l1, 'bases-de-donnees', 'Bases de données',
            'Modèle relationnel, algèbre, SQL et conception de schémas. L\'étudiant apprend à passer d\'un besoin métier à des tables cohérentes.');

        $this->chapterWithQuiz($bdd, 1, 'Le modèle relationnel', <<<'TXT'
Une base de données relationnelle organise l'information en tables (relations). Chaque table a un nom, des attributs (colonnes) typés et des lignes (tuples). Une clé primaire identifie de manière unique chaque ligne. Une clé étrangère relie une table à une autre et exprime une association (un étudiant appartient à une faculté).

Les règles d'intégrité évitent les données orphelines : on ne supprime pas une faculté tant que des étudiants y sont rattachés, sauf politique de cascade clairement choisie. La redondance (répéter le nom de la faculté dans chaque ligne étudiant) est une source d'incohérence ; on la réduit par la normalisation.

Le langage SQL permet d'interroger (SELECT), d'insérer, de modifier et de supprimer. La clause WHERE filtre, JOIN relie les tables, GROUP BY agrège. Un bon schéma vaut mieux qu'une requête compliquée écrite pour rattraper un mauvais modèle.
TXT, [
            'title' => 'Interrogation — Relationnel',
            'passing_score' => 60,
            'questions' => [
                [
                    'prompt' => 'À quoi sert une clé primaire ?',
                    'answers' => [
                        ['À trier automatiquement la table par ordre alphabétique', false],
                        ['À identifier de manière unique chaque ligne', true],
                        ['À chiffrer les mots de passe', false],
                    ],
                ],
                [
                    'type' => QuestionType::Text,
                    'prompt' => 'Quel langage d\'interrogation des bases relationnelles est présenté dans le chapitre ?',
                    'accepted' => ['SQL'],
                ],
                [
                    'prompt' => 'Une clé étrangère sert principalement à :',
                    'answers' => [
                        ['Relier une table à une autre', true],
                        ['Accélérer le disque dur', false],
                        ['Remplacer la clé primaire', false],
                    ],
                ],
            ],
        ]);

        $this->chapterWithQuiz($bdd, 2, 'Du besoin métier au schéma', <<<'TXT'
La conception commence par le modèle conceptuel (entités, associations, cardinalités) puis le modèle logique (tables). Une association 1,n se traduit en général par une clé étrangère du côté n. Une association n,n exige une table de liaison.

Exemple universitaire : Faculté 1,n Option ; Option n,n Promotion via les cours. L'étudiant (User) est rattaché à un triplet Faculté / Option / Promotion, ce qui permet d'isoler les contenus pédagogiques.

On vérifie ensuite les formes normales : chaque attribut dépend de la clé, pas d'un autre attribut non clé. Enfin on choisit les index (souvent sur les clés étrangères) pour que les jointures restent rapides en production.
TXT, [
            'title' => 'Interrogation — Conception',
            'passing_score' => 50,
            'questions' => [
                [
                    'prompt' => 'Comment traduit-on en général une association n,n ?',
                    'answers' => [
                        ['Par une simple clé étrangère', false],
                        ['Par une table de liaison', true],
                        ['Par un attribut texte libre', false],
                    ],
                ],
                [
                    'prompt' => 'Dans cette plateforme, un étudiant est isolé par :',
                    'answers' => [
                        ['Son adresse e-mail uniquement', false],
                        ['Le triplet faculté, option et promotion', true],
                        ['Le nom de son enseignant', false],
                    ],
                ],
            ],
        ]);

        $structures = $this->course($faculty, $option, $l2, 'structures-de-donnees', 'Structures de données',
            'Piles, files, listes chaînées, arbres et graphes : choisir la bonne structure selon les opérations dominantes.');

        $this->chapterWithQuiz($structures, 1, 'Piles et files', <<<'TXT'
La pile (stack) suit la discipline LIFO : last in, first out. Les opérations caractéristiques sont empiler (push) et dépiler (pop). Elle sert à l'évaluation d'expressions, à la gestion des appels de fonctions et à l'annulation (undo).

La file (queue) suit la discipline FIFO : first in, first out. On enfile à la fin et on défile au début. Elle modélise une file d'attente (impressions, processus, messages).

Le choix n'est pas cosmétique : un mauvais choix de structure transforme un algorithme linéaire en algorithme quadratique. En L2, on implémente ces structures soit avec un tableau et deux indices, soit avec des cellules chaînées, et on justifie le coût de chaque opération.
TXT, [
            'title' => 'Interrogation — Piles et files',
            'passing_score' => 50,
            'questions' => [
                [
                    'prompt' => 'Quelle discipline caractérise une pile ?',
                    'answers' => [
                        ['FIFO', false],
                        ['LIFO', true],
                        ['Aléatoire', false],
                    ],
                ],
                [
                    'type' => QuestionType::Text,
                    'prompt' => 'Quel mot désigne une file d\'attente FIFO (file ou queue) ?',
                    'accepted' => ['file', 'queue'],
                ],
            ],
        ]);

        $this->chapterWithQuiz($structures, 2, 'Arbres binaires de recherche', <<<'TXT'
Un arbre binaire de recherche (ABR) organise des clés de sorte que, pour tout nœud, les clés du sous-arbre gauche sont inférieures et celles du sous-arbre droit sont supérieures. La recherche, l'insertion et la suppression se font alors en O(h), où h est la hauteur.

Si l'arbre dégénère en une liste (insertions déjà triées), h vaut n et l'avantage disparaît. D'où l'intérêt des arbres équilibrés (AVL, rouge-noir) abordés en fin de licence.

Le parcours infixe d'un ABR produit les clés dans l'ordre croissant. C'est un résultat à retenir : un tri peut s'obtenir en insérant toutes les valeurs dans un ABR puis en parcourant en infixe, au prix d'une hauteur maîtrisée.
TXT, [
            'title' => 'Interrogation — ABR',
            'passing_score' => 50,
            'questions' => [
                [
                    'prompt' => 'Le parcours infixe d\'un ABR produit les clés :',
                    'answers' => [
                        ['Dans un ordre aléatoire', false],
                        ['Dans l\'ordre croissant', true],
                        ['Toujours du plus grand au plus petit', false],
                    ],
                ],
                [
                    'prompt' => 'Si un ABR dégénère en liste, la recherche devient :',
                    'answers' => [
                        ['O(1)', false],
                        ['O(n)', true],
                        ['O(log n) malgré tout', false],
                    ],
                ],
            ],
        ]);

        $genie = $this->course($faculty, $option, $l3, 'genie-logiciel', 'Génie logiciel',
            'Cycle de vie, exigences, tests et qualité. Préparer l\'étudiant à travailler en équipe sur un logiciel maintenu.');

        $this->chapterWithQuiz($genie, 1, 'Exigences et tests', <<<'TXT'
Une exigence décrit un besoin du client de façon vérifiable. On sépare les exigences fonctionnelles (ce que le système fait) des exigences non fonctionnelles (performance, sécurité, accessibilité).

Le test n'est pas une phase collée à la fin : on écrit des cas dès la spécification. Un test unitaire cible une fonction ; un test d'intégration vérifie plusieurs modules ensemble ; un test d'acceptation confronte le logiciel au besoin réel.

En sécurité pédagogique, un principe revient sans cesse : ne jamais faire confiance à une donnée provenant du client (score d'un quiz, identifiant de cours). Toute règle métier importante — ici l'isolation par faculté, option et promotion — doit être appliquée côté serveur.
TXT, [
            'title' => 'Interrogation — Qualité logicielle',
            'passing_score' => 50,
            'questions' => [
                [
                    'prompt' => 'Où doit-on appliquer une règle de sécurité comme l\'isolation académique ?',
                    'answers' => [
                        ['Uniquement dans le CSS', false],
                        ['Côté serveur, jamais en se fiant au navigateur', true],
                        ['Uniquement dans un commentaire du code', false],
                    ],
                ],
                [
                    'prompt' => 'Un test unitaire cible principalement :',
                    'answers' => [
                        ['Toute l\'application déployée en production', false],
                        ['Une fonction ou un module isolé', true],
                        ['Le réseau de l\'université', false],
                    ],
                ],
            ],
        ]);
    }

    private function seedMathematiques(Faculty $faculty, Option $option, Promotion $l1): void
    {
        $analyse = $this->course($faculty, $option, $l1, 'analyse-1', 'Analyse mathématique 1',
            'Fonctions d\'une variable réelle, limites, continuité et dérivation. Socle de la L1 Mathématiques.');

        $this->chapterWithQuiz($analyse, 1, 'Limites et continuité', <<<'TXT'
Soit f une fonction définie au voisinage d'un point a (sauf éventuellement en a). On dit que f tend vers L quand x tend vers a si les valeurs de f(x) se rapprochent de L aussi près que l'on veut.

La continuité en a exige que f soit définie en a et que la limite en a existe et vaille f(a). Intuitivement, on peut tracer le graphe sans lever le crayon au voisinage de a.

Les formes indéterminées classiques (0/0, ∞/∞) se traitent par factorisation, quantité conjuguée ou, plus tard, par le théorème de l'Hôpital. Un étudiant de L1 doit d'abord maîtriser les limites de références (polynômes, rationnelles simples, sin(x)/x en 0).
TXT, [
            'title' => 'Interrogation — Limites',
            'passing_score' => 50,
            'questions' => [
                [
                    'prompt' => 'Pour qu\'une fonction soit continue en a, il faut notamment :',
                    'answers' => [
                        ['Qu\'elle soit dérivable en a', false],
                        ['Qu\'elle soit définie en a et que sa limite en a vaille f(a)', true],
                        ['Qu\'elle soit polynomiale', false],
                    ],
                ],
                [
                    'type' => QuestionType::Text,
                    'prompt' => 'Quelle forme indéterminée classique s\'écrit 0/0 ?',
                    'accepted' => ['0/0', '0 / 0'],
                ],
            ],
        ]);
    }

    private function seedGestion(Faculty $faculty, Option $option, Promotion $l1, Promotion $l2): void
    {
        $compta = $this->course($faculty, $option, $l1, 'comptabilite-generale', 'Comptabilité générale',
            'Principes comptables, bilan, compte de résultat et enregistrement des opérations courantes selon le SYSCOHADA.');

        $this->chapterWithQuiz($compta, 1, 'Le bilan et le compte de résultat', <<<'TXT'
Le bilan photographie le patrimoine de l'entreprise à une date donnée. L'actif décrit les emplois (caisse, créances, immobilisations). Le passif décrit les ressources (capitaux propres, dettes). L'égalité fondamentale est : actif = passif.

Le compte de résultat retrace les flux de la période : produits moins charges = résultat (bénéfice ou perte). Ce résultat alimente ensuite les capitaux propres du bilan.

En contexte OHADA / SYSCOHADA, le plan comptable organise les comptes par classes (classe 1 capitaux, classe 2 immobilisations, classe 5 trésorerie, classe 6 charges, classe 7 produits, etc.). L'étudiant doit savoir classer une opération avant de la journaliser.
TXT, [
            'title' => 'Interrogation — Bilan et résultat',
            'passing_score' => 50,
            'questions' => [
                [
                    'prompt' => 'Quelle égalité fondamentale caractérise le bilan ?',
                    'answers' => [
                        ['Actif = passif', true],
                        ['Charges = produits', false],
                        ['Trésorerie = dettes', false],
                    ],
                ],
                [
                    'prompt' => 'Le compte de résultat mesure principalement :',
                    'answers' => [
                        ['Le patrimoine à une date', false],
                        ['Les flux de produits et de charges de la période', true],
                        ['Le nombre de salariés', false],
                    ],
                ],
                [
                    'type' => QuestionType::Text,
                    'prompt' => 'Quel référentiel comptable africain est cité dans le chapitre (sigle) ?',
                    'accepted' => ['SYSCOHADA', 'OHADA'],
                ],
            ],
        ]);

        $this->chapterWithQuiz($compta, 2, 'La partie double', <<<'TXT'
Toute écriture comptable enregistre au moins un débit et un crédit de même montant total. C'est le principe de la partie double : on ne crée pas de valeur par l'enregistrement, on en déplace la représentation.

Exemple : achat de marchandises à crédit. On débite le compte d'achats (charge) et on crédite le fournisseur (dette). Plus tard, le règlement débitera le fournisseur et créditera la banque.

Les erreurs classiques sont l'inversion débit/crédit et l'oubli d'une TVA collectée ou déductible. Avant de valider un journal, on vérifie que le total des débits égale le total des crédits.
TXT, [
            'title' => 'Interrogation — Partie double',
            'passing_score' => 50,
            'questions' => [
                [
                    'prompt' => 'Dans la partie double, le total des débits doit :',
                    'answers' => [
                        ['Être supérieur aux crédits', false],
                        ['Être égal au total des crédits', true],
                        ['Être toujours nul', false],
                    ],
                ],
                [
                    'prompt' => 'L\'achat de marchandises à crédit :',
                    'answers' => [
                        ['Débite les achats et crédite le fournisseur', true],
                        ['Crédite la caisse uniquement', false],
                        ['N\'a pas d\'écriture tant que la facture n\'est pas payée', false],
                    ],
                ],
            ],
        ]);

        $finance = $this->course($faculty, $option, $l2, 'finance-entreprise', 'Finance d\'entreprise',
            'Valeur, risque, décisions d\'investissement et de financement. Lecture des documents de synthèse pour décider.');

        $this->chapterWithQuiz($finance, 1, 'La valeur actualisée nette', <<<'TXT'
Un investissement se juge à sa capacité à créer de la valeur. La valeur actualisée nette (VAN) est la somme des flux de trésorerie futurs actualisés, diminuée du capital investi.

Le taux d'actualisation reflète le coût du capital et le risque. Une VAN positive signifie que le projet rapporte plus que ce qu'exigent les apporteurs de fonds. Une VAN négative détruit de la valeur : on écarte le projet, même s'il dégage un bénéfice comptable.

Le délai de récupération et le taux de rentabilité interne (TRI) complètent l'analyse mais ne remplacent pas la VAN lorsque les projets sont mutuellement exclusifs ou de durées différentes.
TXT, [
            'title' => 'Interrogation — VAN',
            'passing_score' => 50,
            'questions' => [
                [
                    'prompt' => 'Une VAN positive signifie que :',
                    'answers' => [
                        ['Le projet détruit de la valeur', false],
                        ['Le projet crée de la valeur au-delà du coût du capital', true],
                        ['L\'entreprise n\'a plus de dettes', false],
                    ],
                ],
                [
                    'type' => QuestionType::Text,
                    'prompt' => 'Quel sigle désigne la valeur actualisée nette ?',
                    'accepted' => ['VAN'],
                ],
            ],
        ]);
    }

    private function seedEconomie(Faculty $faculty, Option $option, Promotion $l1): void
    {
        $micro = $this->course($faculty, $option, $l1, 'microeconomie-1', 'Microéconomie 1',
            'Comportement du consommateur et du producteur, offre, demande et équilibre de marché.');

        $this->chapterWithQuiz($micro, 1, 'Offre, demande et équilibre', <<<'TXT'
La courbe de demande décroît : toutes choses égales par ailleurs, le consommateur achète davantage lorsque le prix baisse. La courbe d'offre croît : le producteur est prêt à offrir davantage si le prix monte.

L'équilibre de marché est le couple (prix, quantité) où l'offre égale la demande. Un prix trop élevé crée un excédent ; un prix trop bas crée une pénurie. En concurrence, le prix tend à revenir vers l'équilibre.

Les déplacements de courbes (revenu, préférences, coût des inputs, fiscalité) ne doivent pas être confondus avec un simple mouvement le long de la courbe. Cette distinction est l'erreur la plus fréquente en L1.
TXT, [
            'title' => 'Interrogation — Marché',
            'passing_score' => 50,
            'questions' => [
                [
                    'prompt' => 'À l\'équilibre de marché :',
                    'answers' => [
                        ['L\'offre est toujours nulle', false],
                        ['L\'offre égale la demande', true],
                        ['Le prix est fixé par l\'État uniquement', false],
                    ],
                ],
                [
                    'prompt' => 'Un prix supérieur au prix d\'équilibre tend à créer :',
                    'answers' => [
                        ['Une pénurie', false],
                        ['Un excédent', true],
                        ['Une inflation nulle à coup sûr', false],
                    ],
                ],
            ],
        ]);
    }

    private function seedDroit(Faculty $faculty, Option $option, Promotion $l1): void
    {
        $intro = $this->course($faculty, $option, $l1, 'introduction-au-droit', 'Introduction au droit',
            'Sources du droit, distinction droit public / droit privé, et premiers éléments du droit des obligations.');

        $this->chapterWithQuiz($intro, 1, 'Les sources du droit', <<<'TXT'
Le droit positif est l'ensemble des règles en vigueur. Ses sources formelles principales sont la Constitution, la loi, les règlements, la coutume et, selon les matières, les traités internationaux. La jurisprudence (décisions des cours) interprète et précise ces textes.

On distingue le droit public (État, collectivités, police, fiscalité) et le droit privé (personnes, contrats, famille, sociétés). Le droit OHADA unifie une grande partie du droit des affaires en Afrique : actes uniformes sur le droit commercial général, les sociétés, les sûretés, etc.

Une règle juridique se reconnaît à son caractère obligatoire et à la sanction qui peut l'accompagner (nullité, dommages-intérêts, peine). Ce n'est pas un simple usage social.
TXT, [
            'title' => 'Interrogation — Sources du droit',
            'passing_score' => 50,
            'questions' => [
                [
                    'prompt' => 'Le droit OHADA concerne principalement :',
                    'answers' => [
                        ['Le droit des affaires unifié en Afrique', true],
                        ['Le droit pénal international exclusivement', false],
                        ['Le droit de la circulation routière européenne', false],
                    ],
                ],
                [
                    'prompt' => 'La Constitution, la loi et les règlements sont des :',
                    'answers' => [
                        ['Sources formelles du droit', true],
                        ['Décisions d\'opportunité politique sans valeur', false],
                        ['Usages commerciaux facultatifs', false],
                    ],
                ],
                [
                    'type' => QuestionType::Text,
                    'prompt' => 'Quel sigle désigne l\'organisation d\'harmonisation du droit des affaires en Afrique ?',
                    'accepted' => ['OHADA'],
                ],
            ],
        ]);
    }

    private function faculty(string $slug, string $name, string $code): Faculty
    {
        return Faculty::query()->updateOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'code' => $code],
        );
    }

    private function option(Faculty $faculty, string $slug, string $name): Option
    {
        return Option::query()->updateOrCreate(
            ['faculty_id' => $faculty->id, 'slug' => $slug],
            ['name' => $name],
        );
    }

    private function promotion(string $slug, string $name, int $level): Promotion
    {
        return Promotion::query()->updateOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'level' => $level],
        );
    }

    private function course(
        Faculty $faculty,
        Option $option,
        Promotion $promotion,
        string $slug,
        string $title,
        string $description,
    ): Course {
        return Course::query()->updateOrCreate(
            [
                'faculty_id' => $faculty->id,
                'option_id' => $option->id,
                'promotion_id' => $promotion->id,
                'slug' => $slug,
            ],
            [
                'title' => $title,
                'description' => $description,
                'is_published' => true,
            ],
        );
    }

    private function seedUsers(
        Faculty $sciences,
        Faculty $economie,
        Faculty $droit,
        Option $informatique,
        Option $mathematiques,
        Option $gestion,
        Option $economieOption,
        Option $droitPrive,
        Promotion $l1,
        Promotion $l2,
        Promotion $l3,
    ): void {
        $users = [
            ['alice@univ.test', 'Alice Mbuyi', UserRole::Student, $sciences, $informatique, $l1],
            ['marie@univ.test', 'Marie Kabila', UserRole::Student, $sciences, $informatique, $l1],
            ['joseph@univ.test', 'Joseph Kalala', UserRole::Student, $sciences, $informatique, $l1],
            ['paul@univ.test', 'Paul Mwamba', UserRole::Student, $sciences, $informatique, $l2],
            ['grace@univ.test', 'Grâce Tshibangu', UserRole::Student, $sciences, $informatique, $l3],
            ['nathan@univ.test', 'Nathan Kabeya', UserRole::Student, $sciences, $mathematiques, $l1],
            ['bob@univ.test', 'Bob Kabongo', UserRole::Student, $economie, $gestion, $l1],
            ['sarah@univ.test', 'Sarah Ilunga', UserRole::Student, $economie, $gestion, $l1],
            ['daniel@univ.test', 'Daniel Mutombo', UserRole::Student, $economie, $gestion, $l2],
            ['fatou@univ.test', 'Fatou Ngalula', UserRole::Student, $economie, $economieOption, $l1],
            ['david@univ.test', 'David Lukusa', UserRole::Student, $droit, $droitPrive, $l1],
            ['esther@univ.test', 'Esther Mwamba', UserRole::Student, $droit, $droitPrive, $l1],
            ['claire@univ.test', 'Prof. Claire Nsimba', UserRole::Teacher, $sciences, $informatique, null],
            ['patrick@univ.test', 'Prof. Patrick Mbala', UserRole::Teacher, $sciences, $mathematiques, null],
            ['jean@univ.test', 'Prof. Jean Tshilombo', UserRole::Teacher, $economie, $gestion, null],
            ['helene@univ.test', 'Prof. Hélène Mwadi', UserRole::Teacher, $droit, $droitPrive, null],
            ['admin@univ.test', 'Service pédagogique', UserRole::Admin, null, null, null],
            ['superadmin@capacademia.test', 'Super administrateur CapAcademia', UserRole::SuperAdmin, null, null, null],
            ['admin@capacademia.test', 'Administrateur CapAcademia', UserRole::Admin, null, null, null],
            ['sciences.admin@capacademia.test', 'Admin Faculté des Sciences', UserRole::Admin, $sciences, null, null],
        ];

        foreach ($users as [$email, $name, $role, $faculty, $option, $promotion]) {
            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => 'password',
                    'role' => $role,
                    'faculty_id' => $faculty?->id,
                    'option_id' => $option?->id,
                    'promotion_id' => $promotion?->id,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
