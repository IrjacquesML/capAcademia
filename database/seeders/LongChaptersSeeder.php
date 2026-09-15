<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Option;
use App\Models\Promotion;
use Database\Seeders\Concerns\BuildsChapters;
use Illuminate\Database\Seeder;

class LongChaptersSeeder extends Seeder
{
    use BuildsChapters;

    public function run(): void
    {
        $this->seedPythonCourse();

        $this->appendLessons($this->course('algorithmique-1'), $this->algorithmiqueSuite());
        $this->appendLessons($this->course('bases-de-donnees'), $this->basesDeDonneesSuite());
        $this->appendLessons($this->course('structures-de-donnees'), $this->structuresSuite());
        $this->appendLessons($this->course('genie-logiciel'), $this->genieSuite());
        $this->appendLessons($this->course('analyse-1'), $this->analyseSuite());
        $this->appendLessons($this->course('comptabilite-generale'), $this->comptabiliteSuite());
        $this->appendLessons($this->course('finance-entreprise'), $this->financeSuite());
        $this->appendLessons($this->course('microeconomie-1'), $this->microeconomieSuite());
        $this->appendLessons($this->course('introduction-au-droit'), $this->droitSuite());
        $this->appendLessons($this->course('programmation-python'), $this->pythonSuite());
    }

    private function course(string $slug): Course
    {
        return Course::query()->where('slug', $slug)->firstOrFail();
    }

    private function seedPythonCourse(): void
    {
        $option = Option::query()->where('slug', 'informatique')->firstOrFail();
        $l1 = Promotion::query()->where('slug', 'l1')->firstOrFail();

        Course::query()->updateOrCreate(
            [
                'faculty_id' => $option->faculty_id,
                'option_id' => $option->id,
                'promotion_id' => $l1->id,
                'slug' => 'programmation-python',
            ],
            [
                'title' => 'Programmation impérative en Python',
                'description' => 'Douze chapitres pour apprendre Python : syntaxe, structures, fonctions, fichiers, exceptions et premiers projets.',
                'is_published' => true,
            ],
        );
    }

    private function algorithmiqueSuite(): array
    {
        return [
            $this->lesson(
                'Variables, types et expressions',
                "Une variable est un nom associé à une valeur en mémoire. On distingue les types scalaires (entier, réel, booléen, caractère) et les types composés (tableau, enregistrement). Le type détermine les opérations autorisées : on n'additionne pas un booléen et une chaîne.\n\nUne expression combine des opérandes et des opérateurs. La priorité (multiplication avant addition) et l'associativité évitent les ambiguïtés. Un bon algorithme nomme les variables d'après leur rôle (somme, compteur, trouvé) plutôt que a1, a2, a3.\n\nL'affectation (variable ← expression) évalue d'abord la droite, puis stocke le résultat. Confondre égalité de test et affectation est une erreur classique en L1.",
                'Que fait une affectation ?',
                'Elle évalue l\'expression à droite puis stocke le résultat dans la variable',
                'Elle compare deux variables sans rien modifier',
                'Elle efface définitivement le type de la variable',
                'Quel mot désigne un nom associé à une valeur en mémoire ?',
                ['variable'],
            ),
            $this->lesson(
                'Fonctions et passage de paramètres',
                "Une fonction encapsule un traitement réutilisable : un nom, des paramètres, un corps, éventuellement une valeur de retour. Factoriser évite de copier-coller et simplifie les tests.\n\nLe passage par valeur donne à la fonction une copie : modifier le paramètre ne change pas l'original. Le passage par référence (ou par adresse) permet de modifier l'objet appelant. En Python, les objets mutables (listes) se comportent souvent comme une référence.\n\nUne fonction doit avoir une responsabilité claire. Si vous ne savez pas la nommer par un verbe précis (calculerMoyenne, rechercher), elle fait probablement trop de choses.",
                'Le passage par valeur signifie que :',
                'La fonction travaille sur une copie et ne modifie pas l\'original',
                'La fonction peut toujours modifier la variable de l\'appelant',
                'Les paramètres sont interdits',
            ),
            $this->lesson(
                'Récursivité',
                "Un algorithme récursif se définit en s'appelant lui-même sur une instance plus petite, jusqu'à un cas de base qui s'arrête. Le calcul de n! (n × (n-1)!) et Fibonacci en sont les exemples scolaires.\n\nSans cas de base, la récursion ne termine pas et provoque un débordement de pile. Chaque appel empile un cadre d'activation : d'où un coût mémoire lié à la profondeur.\n\nToute récursion simple peut se réécrire avec une boucle, mais certains parcours d'arbres s'expriment plus naturellement en récursif. On exige en L1 de toujours écrire le cas de base en premier.",
                'Sans cas de base, une fonction récursive :',
                'Ne termine pas et risque de déborder la pile',
                'S\'arrête automatiquement après 10 appels',
                'Se transforme en boucle for',
                'Quel mot désigne l\'appel d\'une fonction à elle-même ?',
                ['récursivité', 'recursion', 'récursion'],
            ),
            $this->lesson(
                'Tris élémentaires',
                "Trier, c'est réordonner des éléments selon une relation d'ordre. Le tri par sélection cherche le minimum et l'échange avec la position courante : O(n²) comparaisons. Le tri par insertion décale les éléments déjà triés pour insérer la valeur suivante : efficace sur des données presque triées.\n\nLe tri à bulles compare les voisins et les échange : simple à expliquer, rarement utilisé en production. En licence, on compare ces tris au tri fusion et au tri rapide (vus en L2) qui visent O(n log n).\n\nUn tri est stable s'il préserve l'ordre relatif des éléments égaux. Cette propriété compte lorsque l'on trie d'abord par nom, puis par moyenne.",
                'Quelle est la complexité typique du tri par sélection ?',
                'O(n²)',
                'O(log n)',
                'O(1)',
            ),
            $this->lesson(
                'Recherche séquentielle et dichotomique',
                "La recherche séquentielle parcourt le tableau depuis le début jusqu'à trouver la clé ou épuiser les cases : O(n) dans le pire cas, aucun prérequis d'ordre.\n\nLa recherche dichotomique exige un tableau trié. On compare la clé à l'élément médian, puis on réduit de moitié la zone de recherche. Le nombre d'étapes est de l'ordre de log₂(n).\n\nErreur fréquente : appliquer la dichotomie sur un tableau non trié. Le résultat est alors non garanti. Toujours documenter les préconditions d'un algorithme.",
                'La recherche dichotomique exige :',
                'Un tableau trié',
                'Un tableau forcément vide',
                'Une pile LIFO',
            ),
            $this->lesson(
                'Chaînes de caractères',
                "Une chaîne est une suite de caractères (lettres, chiffres, symboles). Les opérations usuelles sont la concaténation, l'extraction d'une sous-chaîne, la recherche d'un motif et la comparaison.\n\nSelon les langages, les chaînes sont immuables (Python, Java) ou modifiables (certains usages en C). Immuable signifie qu'une « modification » produit une nouvelle chaîne.\n\nEn algorithmique, on se méfie des indices : le premier caractère est souvent à l'indice 0. Les palindromes, le comptage de voyelles et la normalisation (minuscules, suppression d'accents) sont des exercices types de L1.",
                'Une chaîne immuable :',
                'Ne se modifie pas en place : toute altération crée une nouvelle chaîne',
                'Peut changer de longueur sans nouvelle allocation',
                'Interdit la concaténation',
            ),
            $this->lesson(
                'Fichiers texte',
                "Un fichier texte stocke des caractères accessibles séquentiellement. On ouvre le fichier (lecture, écriture ou ajout), on traite les lignes, puis on le ferme pour libérer la ressource.\n\nL'encodage (UTF-8, Latin-1) doit être explicite dès que l'on quitte l'ASCII. Un fichier mal encodé affiche des caractères corrompus, surtout avec les accents du français.\n\nEn algorithmique, on distingue le traitement ligne à ligne (gros fichiers) du chargement complet en mémoire (fichiers petits). Toujours prévoir le cas « fichier absent » ou « permission refusée ».",
                'Quelle opération doit suivre l\'ouverture d\'un fichier après traitement ?',
                'La fermeture du fichier',
                'La compilation du système',
                'La suppression obligatoire du disque',
                'Quel encodage Unicode courant est recommandé pour le français ?',
                ['UTF-8', 'UTF8', 'utf-8'],
            ),
            $this->lesson(
                'Mise au point et jeux de tests',
                "Debugger, c'est localiser un écart entre le comportement observé et le comportement spécifié. On reproduit le bug, on isole un exemple minimal, on inspecte les variables aux points critiques.\n\nUn jeu de tests couvre les cas nominaux et les cas limites : tableau vide, une seule valeur, valeurs toutes égales, très grandes tailles. Un algorithme « qui marche sur un exemple » n'est pas un algorithme validé.\n\nLes assertions (préconditions, postconditions) documentent le contrat. En plateforme e-learning comme ici, on teste aussi les règles de sécurité : un étudiant d'une autre faculté ne doit pas voir le cours.",
                'Un bon jeu de tests doit inclure :',
                'Les cas nominaux et les cas limites',
                'Uniquement le premier exemple du cours',
                'Aucune donnée, seulement des commentaires',
            ),
            $this->lesson(
                'Modularité et style',
                "Un programme maintenable est découpé en modules cohérents : constantes nommées, fonctions courtes, commentaires utiles (le pourquoi, pas le quoi évident). On évite la magie numérique (écrire MAX_ETUDIANTS = 40 plutôt que 40 partout).\n\nLe style n'est pas cosmétique. Un correcteur, un camarade de projet ou vous-même dans trois mois devez relire sans souffrir. L'indentation, des noms parlants et une seule responsabilité par fonction sont des exigences de L1.\n\nEnfin, on versionne son travail (git) et on ne laisse pas de code mort. La clarté précède la micro-optimisation : d'abord correct, ensuite lisible, ensuite rapide si besoin.",
                'Que faut-il documenter en priorité dans un commentaire ?',
                'Le pourquoi d\'une décision, pas l\'évidence du code',
                'Chaque mot-clé du langage',
                'Le nom de l\'ordinateur utilisé',
            ),
        ];
    }

    private function pythonSuite(): array
    {
        return [
            $this->lesson(
                'Prise en main de Python',
                "Python est un langage interprété, à typage dynamique, très utilisé en enseignement et en science des données. On exécute un script fichier.py ou on expérimente dans l'interpréteur interactif.\n\nL'indentation (espaces en début de ligne) délimite les blocs : pas d'accolades comme en Java. Quatre espaces par niveau sont la convention. Un mélange tabulations/espaces provoque des erreurs de syntaxe.\n\nLe premier programme affiche un message avec print. On commente avec #. L'étudiant installe une distribution récente (3.11+) et un éditeur (VS Code, PyCharm, Idle).",
                'En Python, les blocs d\'instructions sont délimités par :',
                'L\'indentation',
                'Des accolades obligatoires',
                'Le mot-clé begin/end',
                'Quelle fonction affiche un texte à l\'écran ?',
                ['print'],
            ),
            $this->lesson(
                'Types de base et opérations',
                "Les types de base sont int (entiers), float (réels), bool (True/False) et str (chaînes). L'opérateur // est la division entière, % le modulo, ** la puissance.\n\nLe typage est dynamique : une variable peut d'abord référencer un entier puis une chaîne, ce qui est autorisé mais souvent une mauvaise idée. On utilise type(x) ou isinstance pour inspecter.\n\nLes conversions explicites int('42'), float('3.14'), str(7) évitent les surprises. Attention à int('3.14') qui échoue : il faut d'abord passer par float.",
                'Quel opérateur réalise la division entière en Python ?',
                '//',
                '/',
                '%',
            ),
            $this->lesson(
                'Conditions et booléens',
                "Les tests if / elif / else orientent le flux. Les comparaisons ==, !=, <, >, <=, >= produisent des booléens. On combine avec and, or, not.\n\nPython considère comme faux : False, 0, 0.0, '', [], None. Tout le reste est vrai. Cette règle, utile, peut cacher des bugs si l'on teste if liste: pour savoir si elle est non vide.\n\nOn évite if x == True: au profit de if x:. Les conditions composées se parenthèsent pour la lisibilité, surtout avec des or mélangés à des and.",
                'Quelle valeur Python est considérée comme fausse ?',
                'Une liste vide []',
                'Le nombre 2',
                'La chaîne "False"',
            ),
            $this->lesson(
                'Boucles for et while',
                "for x in iterable: parcourt les éléments. range(n) produit 0, 1, …, n-1. range(a, b) s'arrête avant b. while condition: répète tant que la condition est vraie.\n\nbreak sort de la boucle, continue passe à l'itération suivante. Une boucle while mal conçue (condition qui ne devient jamais fausse) tourne indéfiniment.\n\nOn préfère for lorsqu'on connaît la collection à parcourir, while lorsqu'on attend un événement (saisie valide, recherche d'un sentinelle).",
                'range(5) produit :',
                'Les entiers 0, 1, 2, 3, 4',
                'Les entiers 1, 2, 3, 4, 5',
                'Uniquement le nombre 5',
                'Quel mot-clé interrompt immédiatement une boucle ?',
                ['break'],
            ),
            $this->lesson(
                'Listes et tuples',
                "Une liste est mutable : on peut ajouter (append), insérer, supprimer, modifier un indice. Un tuple est immuable : utile pour des paires (nom, note) que l'on ne veut pas altérer par erreur.\n\nLe slicing liste[a:b] extrait une sous-liste. liste[-1] est le dernier élément. L'opérateur + concatène, * répète. Attention : liste1 = liste2 copie la référence, pas le contenu ; on utilise liste.copy() ou liste[:].\n\nLes listes en compréhension [x*x for x in nombres if x > 0] sont idiomatiques. En L1, on les introduit après les boucles classiques.",
                'Quelle structure est immuable en Python ?',
                'Le tuple',
                'La liste',
                'Le dictionnaire',
            ),
            $this->lesson(
                'Dictionnaires et ensembles',
                "Un dictionnaire associe des clés uniques à des valeurs : etudiant = {'nom': 'Mbuyi', 'note': 14}. L'accès se fait en temps moyen constant. keys(), values(), items() parcourent le contenu.\n\nUn ensemble (set) stocke des éléments uniques, sans ordre. Il sert à supprimer les doublons et à tester l'appartenance rapidement. Les opérations |, &, - correspondent à union, intersection, différence.\n\nLes clés d'un dict doivent être hachables (immuables) : une liste ne peut pas servir de clé, un tuple le peut.",
                'Que stocke un dictionnaire Python ?',
                'Des couples clé → valeur avec clés uniques',
                'Uniquement une suite ordonnée d\'entiers',
                'Des fichiers binaires',
                'Quel type Python désigne un ensemble ?',
                ['set'],
            ),
            $this->lesson(
                'Fonctions, portée et modules',
                "def moyenne(valeurs): définit une fonction. return renvoie un résultat. Les variables créées dans la fonction sont locales, sauf déclaration global (à éviter en L1).\n\nOn documente avec une docstring (\"\"\"...\"\"\"). Les arguments par défaut (def f(x, base=10)) se placent après les arguments obligatoires. On n'utilise jamais une liste mutable comme valeur par défaut.\n\nimport math charge un module. from math import sqrt importe un nom. Un projet se découpe en fichiers : un module par responsabilité.",
                'Une variable créée à l\'intérieur d\'une fonction est par défaut :',
                'Locale à cette fonction',
                'Visible dans tout le programme',
                'Supprimée du disque',
            ),
            $this->lesson(
                'Fichiers et JSON',
                "with open('notes.csv', encoding='utf-8') as f: garantit la fermeture du fichier même en cas d'erreur. On lit avec f.read() ou for ligne in f.\n\nLe format CSV sépare les champs par des virgules ; le module csv gère les cas délicats (virgules dans un champ). JSON (json.dumps / json.loads) échange des structures avec le web ou une API.\n\nToujours spécifier encoding='utf-8' pour les textes français. Un chemin relatif dépend du répertoire de travail : en production on utilise pathlib.",
                'Pourquoi utiliser with open(...) as f ?',
                'Pour fermer automatiquement le fichier, y compris en cas d\'erreur',
                'Pour accélérer le processeur',
                'Pour convertir le fichier en image',
                'Quel encodage faut-il préciser pour un fichier texte en français ?',
                ['utf-8', 'UTF-8'],
            ),
            $this->lesson(
                'Exceptions',
                "Une exception signale un incident : division par zéro, fichier introuvable, conversion impossible. try / except intercepte l'erreur et propose un plan B plutôt que de faire planter tout le script.\n\nOn capture des types précis (ValueError, FileNotFoundError) plutôt qu'un except trop large qui masquerait un vrai bug. else s'exécute si aucune exception, finally toujours.\n\nraise ValueError('note invalide') déclenche volontairement une erreur lorsque les données violent un contrat (note hors de 0–20).",
                'Que fait un bloc try/except ?',
                'Il intercepte une erreur pour la traiter sans arrêter brutalement le programme',
                'Il accélère les boucles',
                'Il compile le code en langage machine',
            ),
            $this->lesson(
                'Algorithmes classiques en Python',
                "On réimplémente en Python la recherche linéaire, la dichotomie, le tri par insertion et le calcul de statistiques (min, max, moyenne, écart-type). L'objectif n'est pas d'égaler sorted() de la bibliothèque, mais de comprendre.\n\nLes listes Python ont déjà sort() et sorted(). En devoir, on peut exiger un tri écrit à la main. En projet, on utilise la bibliothèque standard.\n\nMesurer le temps (time.perf_counter) sur n = 10³, 10⁴, 10⁵ illustre concrètement O(n²) contre O(n log n).",
                'sorted() en Python :',
                'Renvoie une nouvelle liste triée, sans modifier l\'originale',
                'Efface tous les doublons uniquement',
                'Convertit la liste en dictionnaire',
            ),
            $this->lesson(
                'Bonnes pratiques et style PEP 8',
                "PEP 8 est le guide de style officiel : noms de fonctions en snake_case, classes en PascalCase, constantes en MAJUSCULES, lignes de 79–99 caractères, deux lignes entre fonctions de haut niveau.\n\nLes outils ruff, flake8 ou black automatisent une partie des conventions. En binôme, un style commun évite les guerres d'indentation.\n\nOn écrit des tests (assert ou pytest) pour les fonctions de calcul. Un script « qui affiche » sans fonction testable est difficile à noter et à faire évoluer.",
                'PEP 8 est :',
                'Le guide de style officiel de Python',
                'Un type de donnée numérique',
                'Un serveur web',
                'Quel style de nommage utilise-t-on pour une fonction Python ?',
                ['snake_case', 'snake case'],
            ),
            $this->lesson(
                'Mini-projet : carnet de notes',
                "Le mini-projet de fin de cours consiste à gérer les notes d'une promotion : saisir un étudiant, enregistrer une note entre 0 et 20, calculer la moyenne, afficher les admis (note ≥ 10), sauvegarder en JSON.\n\nOn structure le code en fonctions (ajouter, lister, moyenne, sauvegarder) et on valide les saisies. Une note 21 doit être rejetée. Le fichier JSON permet de reprendre le travail à la prochaine séance.\n\nCe projet relie tout le semestre : listes, dictionnaires, fichiers, exceptions, fonctions. Il prépare aussi le cours de bases de données, où les mêmes informations deviendront des tables.",
                'Une note saisie à 21 doit :',
                'Être rejetée par la validation',
                'Être convertie automatiquement en 0',
                'Être stockée sans contrôle',
            ),
        ];
    }

    private function basesDeDonneesSuite(): array
    {
        return [
            $this->lesson(
                'Le langage SQL : SELECT',
                "SELECT choisit les colonnes, FROM la table, WHERE les lignes. DISTINCT élimine les doublons. ORDER BY trie. LIMIT (ou FETCH) borne le nombre de résultats.\n\nLes alias (AS) rendent les requêtes lisibles. Les comparaisons LIKE '%info%' cherchent un motif. BETWEEN filtre un intervalle, IN une liste de valeurs, IS NULL teste l'absence.\n\nOn n'écrit jamais SELECT * en production sans raison : on nomme les colonnes utiles. Une requête se lit comme une phrase : « quelles colonnes, de quelle table, sous quelle condition ».",
                'Quelle clause SQL filtre les lignes ?',
                'WHERE',
                'GROUP BY',
                'DROP',
                'Quelle instruction SQL lit des données ?',
                ['SELECT'],
            ),
            $this->lesson(
                'Les jointures',
                "INNER JOIN ne conserve que les lignes qui matchent des deux côtés. LEFT JOIN conserve toutes les lignes de la table de gauche, avec des NULL à droite s'il n'y a pas de correspondance.\n\nOn joint en général sur une clé primaire = clé étrangère : etudiants.faculty_id = faculties.id. Oublier la condition de jointure produit un produit cartésien : une explosion de lignes.\n\nPlusieurs jointures s'enchaînent pour reconstruire le triplet faculté / option / promotion d'un cours. C'est exactement le modèle de cette plateforme.",
                'Un INNER JOIN conserve :',
                'Uniquement les lignes qui ont une correspondance des deux côtés',
                'Toutes les lignes des deux tables, toujours',
                'Uniquement les colonnes numériques',
            ),
            $this->lesson(
                'Agrégats et GROUP BY',
                "COUNT, SUM, AVG, MIN, MAX résument un ensemble de lignes. GROUP BY partitionne : moyenne des notes par option, nombre d'étudiants par faculté.\n\nHAVING filtre les groupes (après agrégation), alors que WHERE filtre les lignes (avant). On écrit HAVING COUNT(*) > 10, pas WHERE COUNT(*) > 10.\n\nToute colonne du SELECT non agrégée doit figurer dans GROUP BY (mode strict). C'est une règle à retenir pour éviter des résultats non déterministes.",
                'HAVING sert à :',
                'Filtrer des groupes après agrégation',
                'Créer une table',
                'Renommer une base',
            ),
            $this->lesson(
                'Mise à jour des données',
                "INSERT INTO ajoute des lignes, UPDATE les modifie, DELETE les supprime. Un UPDATE ou DELETE sans WHERE est une catastrophe : toute la table est affectée.\n\nLes transactions (BEGIN, COMMIT, ROLLBACK) permettent d'annuler un lot d'écritures si une étape échoue. En MySQL/InnoDB et en PostgreSQL, c'est le comportement attendu pour les opérations métier.\n\nLes contraintes (NOT NULL, UNIQUE, FOREIGN KEY, CHECK) rejettent les données incohérentes avant qu'elles n'entrent dans la base.",
                'Que risque un DELETE sans WHERE ?',
                'De supprimer toutes les lignes de la table',
                'De créer un index',
                'De convertir la table en vue',
                'Quelle instruction ajoute une ligne ?',
                ['INSERT', 'INSERT INTO'],
            ),
            $this->lesson(
                'Contraintes d\'intégrité',
                "NOT NULL interdit l'absence de valeur. UNIQUE garantit l'unicité (e-mail d'un utilisateur). PRIMARY KEY combine unicité et NOT NULL. FOREIGN KEY empêche d'orpheliner une référence.\n\nON DELETE RESTRICT refuse de supprimer un parent encore référencé. CASCADE propage la suppression. SET NULL met la clé étrangère à NULL. Le choix est un choix métier, pas cosmétique.\n\nCHECK (note BETWEEN 0 AND 20) exprime une règle de domaine. Mieux vaut la coller en base qu'espérer que chaque formulaire sera parfait.",
                'Une clé étrangère sert à :',
                'Garantir qu\'une référence pointe vers une ligne existante',
                'Chiffrer les mots de passe',
                'Accélérer le réseau',
            ),
            $this->lesson(
                'Index et performance',
                "Un index est une structure auxiliaire (souvent un B-arbre) qui accélère les recherches et les jointures sur une colonne. Les clés primaires et étrangères sont de bons candidats.\n\nTrop d'index ralentit les INSERT/UPDATE : chaque écriture met à jour les index. On indexe ce que l'on filtre vraiment.\n\nEXPLAIN (PostgreSQL, MySQL) montre si une requête utilise un index ou un parcours de table. Un LIKE '%terme' en début de motif ne peut en général pas utiliser un index B-arbre classique.",
                'Un index accélère principalement :',
                'Les recherches et jointures sur les colonnes indexées',
                'La vitesse d\'impression papier',
                'Le compilateur PHP',
            ),
            $this->lesson(
                'Transactions et concurrence',
                "ACID : atomicité (tout ou rien), cohérence, isolation, durabilité. Isolation empêche qu'une transaction voie les écritures non validées d'une autre.\n\nLes niveaux d'isolation (READ COMMITTED, REPEATABLE READ, SERIALIZABLE) arbitrent entre cohérence et débit. Un mauvais niveau produit des lectures sales ou des anomalies de phantom read.\n\nEn application web, une correction de quiz (calcul du score + enregistrement + progression) doit tenir dans une transaction : c'est ce que fait déjà le service de notation de cette plateforme.",
                'ACID, la lettre A signifie :',
                'Atomicité : tout le lot réussit ou tout est annulé',
                'Affichage des index',
                'Ajout automatique de colonnes',
            ),
            $this->lesson(
                'Vues et sécurité SQL',
                "Une vue est une requête nommée. Elle simplifie les accès (vue_etudiants_l1) et peut masquer des colonnes sensibles. Certaines vues sont actualisables, d'autres non.\n\nLe principe du moindre privilège : un compte application n'a pas DROP DATABASE. On sépare le compte de migration (DDL) du compte d'exécution (DML limité).\n\nL'injection SQL se prévient par des requêtes paramétrées (ORM, prepared statements), jamais par la concaténation de saisies utilisateur dans une chaîne SQL.",
                'Comment prévient-on l\'injection SQL ?',
                'En utilisant des requêtes paramétrées, jamais la concaténation de saisies',
                'En mettant tous les mots de passe en clair',
                'En désactivant WHERE',
            ),
        ];
    }

    private function structuresSuite(): array
    {
        return [
            $this->lesson(
                'Listes chaînées',
                "Une liste chaînée relie des cellules par des pointeurs. L'insertion en tête est O(1) ; l'accès au k-ième élément est O(k), contrairement au tableau.\n\nLa liste doublement chaînée mémorise prédécesseur et successeur, ce qui simplifie la suppression. On veille au cas de la liste vide et au dernier élément.\n\nChoisir tableau ou liste dépend des opérations dominantes : beaucoup d'accès aléatoires → tableau ; beaucoup d'insertions en tête → liste.",
                'L\'accès au k-ième élément d\'une liste simplement chaînée est :',
                'O(k)',
                'O(1) comme dans un tableau',
                'O(log k)',
            ),
            $this->lesson(
                'Tables de hachage',
                "Une table de hachage associe une clé à un indice via une fonction de hachage, puis gère les collisions (chaînage ou adressage ouvert). En moyenne, recherche et insertion sont O(1).\n\nUne mauvaise fonction (beaucoup de collisions) dégrade la structure vers O(n). Le facteur de charge (nombre d'éléments / capacité) doit rester borné ; on redimensionne au besoin.\n\nLes dictionnaires Python et HashMap Java reposent sur ce principe. Les clés doivent être immuables et bien réparties.",
                'En moyenne, la recherche dans une bonne table de hachage est :',
                'O(1)',
                'O(n²)',
                'O(n!)',
            ),
            $this->lesson(
                'Tas binaire et file de priorité',
                "Un tas (heap) est un arbre binaire presque complet où chaque nœud est plus prioritaire que ses enfants. Il s'implémente dans un tableau : enfants de i aux indices 2i+1 et 2i+2.\n\nUne file de priorité extrait en O(log n) l'élément d'extrême priorité. Dijkstra et certains ordonnanceurs s'en servent.\n\nLe tas-min et le tas-max ne changent que la relation d'ordre. On ne confond pas tas binaire et zone mémoire « heap » du système.",
                'Un tas binaire s\'implémente naturellement :',
                'Dans un tableau, via les indices des enfants',
                'Uniquement dans une base SQL',
                'Comme une pile LIFO stricte',
            ),
            $this->lesson(
                'Graphes : représentations',
                "Un graphe est un ensemble de sommets et d'arêtes (orientées ou non, pondérées ou non). La matrice d'adjacence occupe O(n²) : pratique si le graphe est dense. La liste d'adjacence occupe O(n+m) : adaptée aux graphes creux.\n\nUn graphe non orienté a une matrice symétrique. Une boucle est une arête d'un sommet vers lui-même. Un chemin simple ne répète pas de sommet.\n\nModéliser un réseau routier, un cursus (prérequis) ou un organigramme commence par ce choix de représentation.",
                'Pour un graphe creux, on privilégie :',
                'La liste d\'adjacence',
                'Toujours une matrice n×n pleine',
                'Un unique tableau de caractères',
            ),
            $this->lesson(
                'Parcours en largeur et en profondeur',
                "Le parcours en largeur (BFS) utilise une file : il visite d'abord les voisins, niveau par niveau. Il calcule les plus courts chemins en nombre d'arêtes.\n\nLe parcours en profondeur (DFS) utilise une pile (ou la récursion) : il s'enfonce dans une branche avant de revenir. Il détecte les cycles et produit des ordres topologiques sur les DAG.\n\nOn marque les sommets visités pour ne pas boucler. Ces deux parcours sont les outils de base de la L2.",
                'Le BFS s\'appuie principalement sur :',
                'Une file (FIFO)',
                'Un tas de fichiers texte',
                'Uniquement une récursion non bornée',
                'Quel parcours visite niveau par niveau ?',
                ['BFS', 'largeur', 'parcours en largeur'],
            ),
            $this->lesson(
                'Plus courts chemins : Dijkstra',
                "L'algorithme de Dijkstra calcule les plus courts chemins depuis une source dans un graphe à poids positifs. Il extrait itérativement le sommet de distance provisoire minimale (file de priorité).\n\nDes poids négatifs rendent Dijkstra incorrect ; on utilise alors Bellman-Ford. Un graphe de transport (distances, durées) a presque toujours des poids positifs.\n\nLa complexité avec un tas binaire est O((n+m) log n). Comprendre l'invariant « les sommets extraits ont leur distance définitive » est l'essentiel de l'examen.",
                'Dijkstra exige des poids :',
                'Positifs (ou nuls)',
                'Strictement négatifs',
                'Uniquement entiers pairs',
            ),
            $this->lesson(
                'Arbres n-aires et tas d\'applications',
                "Un arbre n-aire généralise l'arbre binaire : un nœud peut avoir un nombre variable d'enfants (arborescence de fichiers, organigramme, sommaire de cours).\n\nOn les parcourt en préfixe, infixe (si binaire) ou postfixe selon le besoin (évaluation d'expression, copie, destruction).\n\nBeaucoup de structures vues (tas, ABR, fichiers) sont des arbres spécialisés. Savoir reconnaître un arbre derrière un problème est une compétence de L2.",
                'Un organigramme d\'entreprise se modélise naturellement par :',
                'Un arbre (hiérarchie)',
                'Une unique file FIFO',
                'Un tableau d\'entiers sans lien',
            ),
            $this->lesson(
                'Choix d\'une structure',
                "Avant de coder, on dresse les opérations fréquentes : accès par indice, recherche par clé, insertion, suppression, min/max, parcours. On choisit alors tableau, liste, hachage, ABR, tas ou graphe.\n\nUn carnet d'adresses consulté par nom → hachage ou ABR. Une file d'impression → file. L'historique d'un éditeur → pile. Un réseau social → graphe.\n\nLa « meilleure » structure n'existe pas dans l'absolu. Elle existe pour un cahier des charges. C'est la question de synthèse du cours.",
                'L\'historique Annuler d\'un éditeur se modélise par :',
                'Une pile (LIFO)',
                'Une table SQL uniquement',
                'Un graphe complet obligatoire',
            ),
        ];
    }

    private function genieSuite(): array
    {
        return [
            $this->lesson(
                'Cycle de vie logiciel',
                "Le cycle en cascade enchaîne spécification, conception, développement, tests, déploiement. Il convient mal lorsque le besoin bouge. Les approches itératives (agile) livrent de petits incrémentations fréquentes.\n\nUn incrément doit être potentiellement livrable : pas seulement des slides. On mesure l'avancement par des fonctionnalités testées, pas par des pourcentages de lignes.\n\nEn projet universitaire, un kanban simple (à faire / en cours / testé / livré) suffit. L'important est la visibilité, pas l'outil.",
                'Une approche agile privilégie :',
                'Des incréments fréquents potentiellement livrables',
                'Une seule livraison après deux ans sans feedback',
                'L\'absence de tests',
            ),
            $this->lesson(
                'Recueil des exigences',
                "On interviewe les utilisateurs, on observe leur travail, on rédige des user stories (« En tant qu'étudiant, je veux ne voir que mes cours ») et des critères d'acceptation.\n\nUne exigence vague (« le site doit être rapide ») se reformule de façon mesurable (« 95 % des pages < 2 s sur le réseau campus »). Ce qui n'est pas mesurable n'est pas testable.\n\nLes règles de sécurité (isolation faculté / option / promotion) sont des exigences, pas des détails d'implémentation à improviser en fin de projet.",
                'Une bonne exigence est surtout :',
                'Vérifiable et mesurable',
                'Volontairement floue pour garder de la marge',
                'Rédigée uniquement après la mise en production',
            ),
            $this->lesson(
                'Conception et architecture',
                "On sépare présentation, domaine et persistance (MVC, couches). Un contrôleur ne calcule pas un score de quiz dans la vue ; un service métier le fait, de façon testable.\n\nLes diagrammes (cas d'utilisation, classes, séquence) communiquent une intention. Ils ne remplacent pas le code, ils évitent de partir dans tous les sens.\n\nUne architecture trop complexe pour un projet L3 est aussi un défaut. La simplicité qui respecte les responsabilités est une qualité.",
                'Le score d\'un quiz doit être calculé :',
                'Dans un service métier côté serveur',
                'Uniquement dans le navigateur, sans contrôle',
                'Par l\'étudiant qui saisit son propre barème',
            ),
            $this->lesson(
                'Stratégies de test',
                "La pyramide des tests : beaucoup d'unitaires, moins d'intégration, peu de bout-en-bout. Les tests unitaires sont rapides et localisent le bug. Les tests E2E sont lents et fragiles, mais rassurent sur le parcours réel.\n\nOn teste les cas d'erreur (étudiant d'une autre faculté → 404) autant que le cas heureux. Un test qui ne peut pas échouer ne sert à rien.\n\nLa couverture de code est un indicateur, pas un objectif de 100 % cosmétique. On couvre d'abord les règles métier critiques.",
                'Un test qui ne peut jamais échouer :',
                'Ne sert à rien',
                'Prouve que le logiciel est parfait',
                'Remplace la production',
            ),
            $this->lesson(
                'Revue de code et qualité',
                "Une revue de code (pull request) fait relire le changement par un pair avant fusion. On cherche la clarté, les régressions, les secrets commitées, les oublis de tests.\n\nLes linters (Pint en PHP, Ruff en Python) éliminent les débats de virgules. L'énergie humaine va au métier et à la sécurité.\n\nLa dette technique n'est pas un crime si elle est consciente et remboursée. Elle devient un problème lorsqu'on ne peut plus faire évoluer le module d'inscription.",
                'Une revue de code vise surtout à :',
                'Détecter erreurs, ambiguïtés et risques avant la fusion',
                'Remplacer tous les tests automatisés',
                'Ralentir volontairement sans objectif',
            ),
            $this->lesson(
                'Déploiement et exploitation',
                "Déployer, c'est installer une version sur un environnement (staging, production) de façon reproductible : migrations de base, variables d'environnement, journaux, sauvegardes.\n\nOn ne débogue pas en production en éditant les fichiers à la main. On corrige, on teste, on redéploie. Les secrets (.env) ne vont jamais sur un dépôt public.\n\nUn runbook décrit que faire si le site est down : qui appeler, comment relancer la file d'attente, où sont les sauvegardes SQLite ou MySQL.",
                'Les secrets de production (mots de passe, clés) :',
                'Restent hors du dépôt git, dans l\'environnement',
                'Sont commités en clair pour aider l\'équipe',
                'S\'écrivent dans les vues Blade',
            ),
            $this->lesson(
                'Éthique et données personnelles',
                "Une plateforme universitaire traite des notes, identités, filières. Ce sont des données personnelles. On collecte le minimum, on restreint les accès, on journalise les consultations sensibles.\n\nUn enseignant voit sa faculté, pas nécessairement toute l'université. Un étudiant ne voit pas le corrigé d'un camarade. Ces règles sont à la fois juridiques et pédagogiques.\n\nL'éthique du développeur, c'est aussi refuser un « petit contournement » qui casse l'isolation académique pour aller plus vite.",
                'Un étudiant peut consulter :',
                'Son propre corrigé d\'interrogation, pas celui d\'un camarade',
                'Toutes les notes de la promotion sans restriction',
                'Les mots de passe des enseignants',
            ),
        ];
    }

    private function analyseSuite(): array
    {
        return [
            $this->lesson(
                'Fonctions usuelles',
                "Les polynômes, rationnelles, exponentielle, logarithme népérien, sinus et cosinus forment le catalogue de L1. On connaît leur domaine, leurs limites aux bornes et leur parité.\n\nexp et ln sont réciproques : ln(exp(x)) = x. La relation fondamentale cos² + sin² = 1 sert constamment en trigonométrie.\n\nTracer un graphe commence par le domaine, les asymptotes, le signe de la dérivée, puis un tableau de variations. Le dessin n'est pas un luxe : il guide le raisonnement.",
                'exp et ln sont :',
                'Des fonctions réciproques l\'une de l\'autre',
                'Identiques pour tout réel',
                'Non définies sur R',
            ),
            $this->lesson(
                'Dérivation',
                "La dérivée en a est la limite du taux de variation [f(a+h)-f(a)]/h quand h → 0. Graphiquement, c'est la pente de la tangente. Les formules (somme, produit, quotient, composée) se mémorisent par l'usage.\n\nUne fonction dérivable en a est continue en a. La réciproque est fausse : |x| est continue en 0, non dérivable.\n\nLa dérivée s'annule souvent (pas toujours) aux extremums locaux. On combine f' et f'' (si elle existe) pour préciser min/max.",
                'Une fonction dérivable en a est nécessairement :',
                'Continue en a',
                'Polynomiale',
                'Discontinue en a',
                'Comment appelle-t-on la pente de la tangente au graphe ?',
                ['dérivée', 'derivee', 'nombre dérivé'],
            ),
            $this->lesson(
                'Étude de fonctions',
                "Une étude complète comporte : domaine, parité, périodicité, limites, asymptotes, dérivée, tableau de variations, convexité éventuelle, graphe.\n\nOn factorise f' pour trouver son signe. Un tableau de variations mal signé fausse tout le graphe. On vérifie la cohérence avec les limites.\n\nLes exercices types : rationnelles, composées ln o polynôme, racines. On rédige avec des implications claires, pas des flèches magiques.",
                'Le signe de f\' sert principalement à :',
                'Déterminer les variations de f',
                'Calculer une intégrale définie automatiquement',
                'Prouver qu\'une suite converge vers +∞',
            ),
            $this->lesson(
                'Développements limités : intuition',
                "Au voisinage d'un point, une fonction régulière ressemble à son polynôme de Taylor. DL0(0) de exp, sin, cos, ln(1+x) sont des formules à connaître.\n\nUn DL permet de lever des formes indéterminées et de comparer deux infiniment petits. L'ordre du DL doit être suffisant pour voir le premier terme non nul.\n\nOn ne confond pas un DL (local) avec une série entière (somme infinie sur un rayon). En L1, le DL est un outil de calcul, pas encore la théorie complète.",
                'Un développement limité décrit f :',
                'Au voisinage d\'un point, par un polynôme plus un reste',
                'Sur tout R, exactement, pour toute fonction',
                'Uniquement pour les suites arithmétiques',
            ),
            $this->lesson(
                'Primitives et intégrales',
                "Une primitive de f est une fonction F telle que F' = f. L'intégrale définie ∫_a^b f est, pour f continue, F(b)-F(a) (théorème fondamental).\n\nL'intégrale s'interprète comme une aire algébrique sous la courbe. Linéarité et relation de Chasles sont les premières propriétés.\n\nOn mémorise les primitives usuelles (x^n, 1/x, exp, sin, cos) avant les techniques (parties, substitution) de L2.",
                'Si F est une primitive de f continue, ∫_a^b f(t) dt vaut :',
                'F(b) - F(a)',
                'F(a) + F(b)',
                'F\'(a) × F\'(b)',
            ),
            $this->lesson(
                'Équations différentielles linéaires',
                "y' = ay + b (a, b constants) se résout par exponentielle. L'équation y' = f(x) se ramène à une primitive. On ajoute la constante, puis on la calcule avec la condition initiale.\n\nCes équations modélisent une croissance, un refroidissement, un circuit RC. Lire l'équation, c'est déjà faire un peu de physique ou d'économie.\n\nEn examen, on exige la méthode : solution générale, puis particulière, puis application de y(x0)=y0.",
                'La constante d\'une équation différentielle du premier ordre se fixe grâce :',
                'À une condition initiale',
                'Au théorème de Pythagore uniquement',
                'À la parité d\'un polynôme de degré 4',
            ),
            $this->lesson(
                'Suites numériques',
                "Une suite (u_n) est une fonction de N dans R. On étudie monotonie, bornes, convergence. Une suite croissante majorée converge (théorème de la limite monotone).\n\nLes suites arithmétiques (u_{n+1}=u_n+r) et géométriques (u_{n+1}=q u_n) ont des formules fermées. |q|<1 implique q^n → 0.\n\nBeaucoup de raisonnements d'analyse reposent sur des suites (accroissements, séries plus tard). Savoir majorer |u_n - L| est le cœur de la convergence.",
                'Une suite géométrique de raison q avec |q| < 1 :',
                'Tend vers 0',
                'Tend vers +∞',
                'Oscille entre 2 et 3 uniquement',
            ),
            $this->lesson(
                'Raisonnement et rédaction',
                "L'analyse se rédige : quantificateurs, implications, cas. « Il est évident que » n'est pas une preuve. On cite le théorème utilisé (valeurs intermédiaires, Rolle, accroissements finis).\n\nUn contre-exemple suffit à infirmer une affirmation universelle. Un dessin aide mais ne remplace pas l'écriture.\n\nS'entraîner à relire sa copie comme un correcteur : chaque égalité a-t-elle une justification ? C'est la compétence transversale du semestre.",
                'Pour infirmer une affirmation « pour tout x, P(x) », il suffit :',
                'D\'un contre-exemple',
                'De trois exemples qui marchent',
                'D\'un graphe sans axe',
            ),
            $this->lesson(
                'Théorèmes de Rolle et des accroissements finis',
                "Rolle : si f est continue sur [a,b], dérivable sur ]a,b[, et f(a)=f(b), alors il existe c où f'(c)=0. Les accroissements finis généralisent : f(b)-f(a)=f'(c)(b-a).\n\nCes théorèmes relient le global (valeurs aux bords) et le local (dérivée). Ils servent à encadrer une fonction, à prouver des inégalités, à étudier des suites récurrentes.\n\nLes hypothèses comptent : sans continuité aux bords, Rolle tombe. Un étudiant qui récite la conclusion sans les hypothèses perd les points de rigueur.",
                'Le théorème de Rolle exige notamment :',
                'f(a) = f(b), avec f continue sur [a,b] et dérivable sur ]a,b[',
                'Que f soit une suite géométrique',
                'Que f ne soit pas définie en a',
            ),
        ];
    }

    private function comptabiliteSuite(): array
    {
        return [
            $this->lesson(
                'Le plan comptable SYSCOHADA',
                "Le SYSCOHADA classe les comptes : classe 1 (ressources durables), 2 (actif immobilisé), 3 (stocks), 4 (tiers), 5 (trésorerie), 6 (charges), 7 (produits), 8 (autres charges et produits), 9 (comptabilité analytique, selon les besoins).\n\nUn numéro de compte n'est pas décoratif : 521 évoque une banque, 701 des ventes. L'étudiant apprend à choisir le bon compte avant de passer l'écriture.\n\nLe plan peut être adapté (comptes divisionnaires) tant que l'on respecte la logique des classes. La comparabilité des états financiers en dépend.",
                'La classe 5 du SYSCOHADA concerne principalement :',
                'La trésorerie',
                'Les immobilisations incorporelles uniquement',
                'Les capitaux propres exclusivement',
                'Quel référentiel organise ce plan de comptes ?',
                ['SYSCOHADA', 'OHADA'],
            ),
            $this->lesson(
                'Journal, grand livre et balance',
                "Le journal enregistre chronologiquement les écritures. Le grand livre les ventile par compte. La balance liste, pour chaque compte, les totaux débit/crédit et le solde.\n\nSi la balance n'est pas équilibrée, une erreur de partie double s'est glissée. Ce n'est pas encore la preuve que tout est juste (on peut se tromper de compte des deux côtés), mais c'est un premier filtre.\n\nEn logiciel, ces trois vues existent toujours, même si l'étudiant ne « tient plus » un journal papier. Comprendre le flux reste indispensable.",
                'La balance permet surtout de :',
                'Vérifier l\'équilibre débit/crédit par compte',
                'Remplacer le bilan définitif',
                'Calculer l\'impôt des salariés',
            ),
            $this->lesson(
                'TVA collectée et déductible',
                "La TVA collectée est facturée aux clients (dette envers l'État). La TVA déductible est supportée sur les achats (créance). Le solde se reverse ou se reporte selon les règles fiscales en vigueur.\n\nUne écriture de vente TTC sépare le HT (produit) et la TVA (compte de tiers). Une écriture d'achat sépare la charge HT et la TVA déductible.\n\nL'étudiant doit lire une facture : assiette, taux, mention légale. Une TVA mal ventilée fausse le résultat et la trésorerie.",
                'La TVA collectée est :',
                'Une dette envers l\'État, facturée aux clients',
                'Un produit d\'exploitation définitif',
                'Une immobilisation corporelle',
            ),
            $this->lesson(
                'Achats, ventes et stocks',
                "Les stocks (classe 3) varient : inventaire intermittent (variation constatée en fin de période) ou inventaire permanent (suivi à chaque mouvement). Le choix change le rythme des écritures, pas l'idée.\n\nLe coût d'acquisition d'un stock comprend le prix d'achat net et les frais accessoires. On n'enregistre pas un stock à sa valeur de revente espérée.\n\nUne sortie de stock peut suivre CUMP ou FIFO (premier entré, premier sorti). La méthode se documente dans l'annexe.",
                'Le stock s\'évalue à :',
                'Son coût d\'acquisition (ou de production), pas à la revente espérée',
                'Sa valeur de marché spéculative uniquement',
                'Zéro tant que la facture client n\'est pas payée',
            ),
            $this->lesson(
                'Immobilisations et amortissements',
                "Une immobilisation sert de façon durable (plus d'un exercice). On l'inscrit à l'actif et on répartit son coût via l'amortissement (usure, obsolescence).\n\nL'amortissement linéaire divise la base amortissable par la durée d'utilité. L'écriture débite une dotation (charge) et crédite un amortissement cumulé (moins l'actif).\n\nConfondre charge immédiatement déductible et immobilisation est une erreur de L1 : un stylo n'est pas un bâtiment, un terrain ne s'amortit généralement pas.",
                'L\'amortissement linéaire répartit le coût :',
                'De façon constante sur la durée d\'utilité',
                'En une seule charge le jour de l\'achat, toujours',
                'Uniquement sur le compte de l\'associé',
            ),
            $this->lesson(
                'Créances clients et dépréciations',
                "Une créance client naît à la facturation, pas au règlement. Si le client douteux apparaît, on déprécie la créance : charge et moins-value d'actif, sans l'effacer tant que la perte n'est pas certaine.\n\nLe lettrage rapproche factures et règlements. Les relances font partie du contrôle interne, pas seulement de la comptabilité.\n\nUne créance en devise se réévalue au cours de clôture : écarts de change, autre chapitre de L2.",
                'Une créance client est constatée :',
                'Dès la facturation, même si le règlement est ultérieur',
                'Uniquement le jour où l\'argent arrive en banque',
                'Jamais, car ce n\'est pas un actif',
            ),
            $this->lesson(
                'Paie et charges de personnel',
                "Le salaire brut n'est pas le net. Les cotisations salariales diminuent le net ; les cotisations patronales sont une charge supplémentaire pour l'employeur.\n\nOn distingue les comptes de rémunération, d'organismes sociaux et de net à payer. Un bulletin de paie est la pièce justificative.\n\nEn projet, on n'invente pas des taux : on s'appuie sur la réglementation du moment et on documente la source.",
                'Les cotisations patronales sont :',
                'Une charge de l\'employeur, en plus du brut',
                'Une réduction du capital social',
                'Un produit exceptionnel',
            ),
            $this->lesson(
                'Trésorerie et rapprochement bancaire',
                "Le compte banque en comptabilité et le relevé bancaire divergent souvent (chèques non débités, frais, prélèvements). Le rapprochement explique l'écart ligne à ligne.\n\nOn n'écrit pas le solde du relevé « à la place » de la comptabilité sans justification. Chaque écart devient une écriture ou un report.\n\nLa trésorerie (classe 5) est vitale : une entreprise profitable au compte de résultat peut déposer le bilan par manque de cash. D'où le lien avec le cours de finance L2.",
                'Le rapprochement bancaire sert à :',
                'Expliquer les écarts entre comptabilité et relevé de banque',
                'Calculer la TVA collectée des clients étrangers uniquement',
                'Remplacer le grand livre des immobilisations',
            ),
            $this->lesson(
                'Opérations de fin d\'exercice',
                "À la clôture, on constate les charges à payer, produits à recevoir, stocks finaux, amortissements, dépréciations, provisions. L'objectif est de rattacher les flux à la bonne période (indépendance des exercices).\n\nUne facture non reçue pour un service déjà consommé devient une charge à payer. Un loyer perçu d'avance est un produit constaté d'avance (dette).\n\nSans ces écritures d'inventaire, le résultat est faux. C'est le cœur du passage du journal courant aux états de synthèse.",
                'Le principe d\'indépendance des exercices impose de :',
                'Rattacher charges et produits à la période concernée',
                'Mélanger tous les exercices dans un seul journal',
                'Ignorer les stocks de clôture',
            ),
            $this->lesson(
                'États financiers et annexe',
                "Le bilan, le compte de résultat et le TAFIRE (ou tableau de flux selon le référentiel) forment le noyau des états. L'annexe explique les méthodes (amortissements, stocks, événements post-clôture).\n\nUn lecteur (banquier, associé, administration) doit pouvoir comparer deux exercices. D'où la permanence des méthodes, sauf changement justifié.\n\nSavoir construire un petit bilan à partir d'une balance de clôture est l'objectif terminal de la L1 Gestion.",
                'L\'annexe des états financiers sert surtout à :',
                'Expliquer les méthodes et donner les informations complémentaires',
                'Remplacer le journal au quotidien',
                'Masquer le résultat aux associés',
            ),
        ];
    }

    private function financeSuite(): array
    {
        return [
            $this->lesson(
                'Lecture financière du bilan',
                "On retraite le bilan en grandes masses : actif immobilisé, actif circulant, trésorerie ; capitaux propres, dettes financières, dettes d'exploitation. Le fonds de roulement, le BFR et la trésorerie nette relient ces masses.\n\nFR - BFR = trésorerie nette. Une entreprise peut avoir un FR positif et pourtant une trésorerie tendue si le BFR explose (stocks, créances).\n\nCette lecture précède tout ratio. Sans comprendre la structure, un ratio est un chiffre orphelin.",
                'L\'égalité de structure de trésorerie s\'écrit :',
                'FR - BFR = trésorerie nette',
                'Actif = charges de l\'exercice',
                'VAN = TRI × n',
            ),
            $this->lesson(
                'Ratios de liquidité et de solvabilité',
                "La liquidité générale (actif circulant / dettes à court terme) indique la capacité à faire face aux échéances proches. La solvabilité (dettes / capitaux propres) éclaire le levier.\n\nUn ratio n'a de sens qu'en comparaison (secteur, historique). Une liquidité « trop » élevée peut signaler une trésorerie oisive.\n\nOn se méfie des maquillages de clôture (window dressing). L'annexe et les flux de trésorerie corrigent le regard.",
                'La liquidité générale se calcule comme :',
                'Actif circulant / dettes à court terme',
                'Capitaux propres / immobilisations incorporelles uniquement',
                'VAN / nombre de salariés',
            ),
            $this->lesson(
                'Flux de trésorerie',
                "On distingue flux d'exploitation, d'investissement et de financement. Un résultat positif avec un flux d'exploitation négatif est un signal d'alerte (créances qui s'envolent, stocks).\n\nLe tableau de flux explique la variation de caisse. Il relie le compte de résultat (accrual) et le cash réel.\n\nLes analystes regardent souvent le free cash flow pour juger ce qui peut être distribué ou réinvesti sans casser le modèle.",
                'Un flux d\'exploitation négatif malgré un bénéfice :',
                'Peut signaler un BFR qui se dégrade',
                'Prouve que la VAN est forcément positive',
                'Interdit toute analyse de ratios',
            ),
            $this->lesson(
                'Coût du capital',
                "Le coût du capital pondère le coût des fonds propres et le coût de la dette après impôt (CMPC / WACC). C'est le taux d'actualisation de référence d'un projet de même risque que l'entreprise.\n\nUn projet plus risqué que l'activité actuelle exige un taux plus élevé. Utiliser le CMPC partout est une erreur de diagnostic.\n\nLe coût des fonds propres se justifie par le rendement exigé des associés (modèle de marché, analogie sectorielle en L2/L3).",
                'Le CMPC (WACC) sert principalement :',
                'De taux d\'actualisation pour un projet de risque comparable à l\'entreprise',
                'À calculer la TVA déductible',
                'À remplacer le journal comptable',
            ),
            $this->lesson(
                'Choix d\'investissement',
                "Outre la VAN, on calcule le TRI (taux qui annule la VAN), l'indice de profitabilité, le délai de récupération. Des projets mutuellement exclusifs se classent d'abord à la VAN, pas au TRI.\n\nDes flux non conventionnels (plusieurs changements de signe) peuvent donner plusieurs TRI. D'où la primauté de la VAN.\n\nL'inflation, les impôts et le BFR lié au projet doivent entrer dans les flux. Oublier le BFR gonfle artificiellement la rentabilité.",
                'Entre projets mutuellement exclusifs, on privilégie en général :',
                'Celui de VAN la plus élevée',
                'Celui du plus long délai de récupération',
                'Celui qui a le plus de TRI différents',
            ),
            $this->lesson(
                'Politique de financement',
                "On finance l'actif par un mélange de fonds propres, dette bancaire, crédit-bail, subventions. Le levier magnifie la rentabilité des capitaux propres… et le risque de faillite.\n\nLa capacité de remboursement se juge sur les flux, pas seulement sur le résultat. Un covenant bancaire peut limiter l'endettement futur.\n\nEn OHADA, certaines sûretés (gage, nantissement, hypothèque) organisent le rang des créanciers. Le juriste et le financier se parlent.",
                'Le levier financier :',
                'Peut augmenter la rentabilité des capitaux propres et le risque',
                'Supprime tout risque de faillite',
                'Est un compte de TVA',
            ),
            $this->lesson(
                'Besoin en fonds de roulement projet',
                "Lancer un projet, c'est souvent avancer des stocks et des créances avant d'encaisser. Ce BFR initial est un flux négatif à intégrer dans la VAN.\n\nEn fin de projet, la récupération du BFR est un flux positif. L'oublier biaise le classement des investissements.\n\nOn relie ici comptabilité (délais clients/fournisseurs) et finance (actualisation). C'est le chapitre de synthèse du cours.",
                'Le BFR initial d\'un projet :',
                'Est un flux de trésorerie négatif à intégrer dans la VAN',
                'Augmente automatiquement le TRI sans effet cash',
                'Se comptabilise uniquement en classe 9',
            ),
        ];
    }

    private function microeconomieSuite(): array
    {
        return [
            $this->lesson(
                'Contrainte budgétaire et préférences',
                "Le consommateur choisit un panier sous une contrainte de revenu : p1 x1 + p2 x2 = R. La pente de la droite budgétaire est -p1/p2.\n\nLes courbes d'indifférence représentent les paniers qui procurent le même niveau d'utilité. Plus on s'éloigne de l'origine (biens désirables), plus l'utilité est élevée.\n\nL'optimum est en général un point de tangence : TMS = rapport des prix. Les solutions en coin existent si un bien n'est pas consommé.",
                'À l\'optimum intérieur du consommateur :',
                'Le TMS égale le rapport des prix',
                'Le revenu est forcément nul',
                'Les prix sont indépendants des biens',
            ),
            $this->lesson(
                'Effets revenu et substitution',
                "Quand le prix d'un bien baisse, deux forces jouent : le bien devient relativement moins cher (substitution) et le pouvoir d'achat réel augmente (revenu).\n\nPour un bien normal, les deux effets vont dans le même sens : la demande augmente. Pour un bien inférieur, l'effet revenu peut s'opposer à l'effet substitution.\n\nGraphiquement, on décompose avec une droite budgétaire intermédiaire parallèle à la nouvelle, tangente à l'ancienne courbe d'indifférence.",
                'Si le prix d\'un bien normal baisse, la quantité demandée :',
                'Augmente (effets substitution et revenu dans le même sens)',
                'Diminue forcément',
                'Devient indépendante du revenu',
            ),
            $this->lesson(
                'Fonction de production et coûts',
                "La fonction de production relie facteurs (travail, capital) et output. Le court terme fixe au moins un facteur ; le long terme les rend tous variables.\n\nLe coût marginal est la dérivée du coût total. Il croise le coût moyen en son minimum. Cette géométrie des coûts est au programme de L1.\n\nLes rendements d'échelle (croissants, constants, décroissants) concernent le long terme, lorsque l'on multiplie tous les inputs.",
                'Le coût marginal croise le coût moyen :',
                'Au minimum du coût moyen',
                'Toujours à l\'origine',
                'Uniquement si le prix est nul',
            ),
            $this->lesson(
                'Concurrence parfaite',
                "En concurrence parfaite, les firmes sont price-taker. La règle de production est prix = coût marginal (sur la partie croissante, au-dessus du minimum du coût variable).\n\nLe profit économique peut être nul au long terme avec libre entrée : les rentes s'érodent. Un profit comptable positif n'est pas contradictoire (rémunération du capital).\n\nC'est un modèle de référence, pas une description fidèle de tous les marchés africains ou mondiaux. Il sert de point de comparaison.",
                'En concurrence parfaite, la firme :',
                'Prend le prix comme donné (price-taker)',
                'Fixe seule le prix de tout le marché',
                'Ignore son coût marginal',
            ),
            $this->lesson(
                'Monopole',
                "Le monopole fait face à la demande du marché. La recette marginale est inférieure au prix (courbe de demande décroissante). Il égalise recette marginale et coût marginal, puis lit le prix sur la demande.\n\nIl en résulte généralement un prix plus élevé et une quantité plus faible qu'en concurrence, d'où une perte sèche.\n\nLa discrimination par les prix (si elle est possible) peut modifier ce diagnostic. En L1, on commence par le monopole simple.",
                'Le monopole égalise :',
                'Recette marginale et coût marginal',
                'Prix et recette totale uniquement',
                'Offre de travail et TVA',
            ),
            $this->lesson(
                'Externalités et biens publics',
                "Une externalité est un effet sur autrui non payé par le marché (pollution, vaccination). Le bien public est non rival et non excluable (éclairage urbain). Le marché seul en produit trop ou trop peu.\n\nPigou propose une taxe/subvention. Coase insiste sur les droits de propriété et la négociation si les coûts de transaction sont faibles.\n\nCes notions relient microéconomie et politiques publiques, très présentes dans les débats de développement.",
                'Un bien public pur est :',
                'Non rival et non excluable',
                'Toujours vendu au coût marginal nul par un monopole privé',
                'Identique à un bien inférieur',
            ),
            $this->lesson(
                'Élasticités',
                "L'élasticité-prix de la demande mesure la sensibilité relative de la quantité au prix. |e| > 1 : demande élastique. |e| < 1 : inélastique. L'élasticité-revenu distingue biens normaux et inférieurs.\n\nCes outils servent à anticiper l'effet d'une taxe, d'une subvention ou d'une hausse de tarif de transport.\n\nOn calcule souvent une élasticité arc entre deux points. Le signe et l'interprétation en mots valent autant que le nombre.",
                'Une demande élastique (|e| > 1) signifie que :',
                'La quantité réagit plus que proportionnellement au prix',
                'Le revenu n\'a aucun effet',
                'L\'offre est forcément nulle',
            ),
        ];
    }

    private function droitSuite(): array
    {
        return [
            $this->lesson(
                'Droit public et droit privé',
                "Le droit public organise l'État, les collectivités, la fiscalité, le droit administratif. Le droit privé organise les rapports entre personnes (civils, commerciaux, du travail dans une large mesure).\n\nUn contrat de fourniture avec l'administration peut relever du droit administratif. Un contrat entre deux commerçants relève du droit OHADA des contrats commerciaux.\n\nCette somme n'est pas qu'académique : elle indique le juge compétent et la procédure. Se tromper de branche, c'est parfois se tromper de tribunal.",
                'Le droit OHADA des contrats commerciaux relève surtout :',
                'Du droit privé des affaires',
                'Du seul droit pénal international',
                'Du droit de l\'urbanisme européen',
            ),
            $this->lesson(
                'Les personnes juridiques',
                "La personnalité juridique s'attache aux personnes physiques (êtres humains) et morales (sociétés, associations, État). Elle confère des droits et des obligations.\n\nUne société OHADA (SARL, SA, SAS, etc.) naît de l'immatriculation. Avant, on parle parfois de société en formation, régime délicat pour les actes passés.\n\nLa capacité (jouir de droits, les exercer) peut être limitée (mineurs, tutelle). Un contrat signé par un incapable s'expose à la nullité.",
                'Une société immatriculée est :',
                'Une personne morale',
                'Uniquement un compte bancaire',
                'Un bien public au sens microéconomique',
            ),
            $this->lesson(
                'L\'acte juridique et le fait juridique',
                "L'acte juridique est une manifestation de volonté destinée à produire des effets de droit (contrat, testament). Le fait juridique produit des effets indépendamment d'une volonté de s'obliger (délit, quasi-contrat, naissance).\n\nCette distinction structure le droit des obligations : responsabilité contractuelle vs extra-contractuelle, régime de preuve, délais.\n\nUn même événement de la vie (un accident de la circulation) peut combiner les deux analyses.",
                'Un contrat est :',
                'Un acte juridique (volonté de produire des effets de droit)',
                'Un fait juridique sans volonté',
                'Une simple conversation sans valeur',
            ),
            $this->lesson(
                'Formation du contrat',
                "Le contrat se forme par l'échange des consentements (offre et acceptation), sur un contenu licite et certain, entre parties capables. Les vices du consentement (erreur, dol, violence) peuvent anéantir le contrat.\n\nEn droit OHADA, la liberté contractuelle est le principe, dans les limites de l'ordre public. Un contrat contraire à une disposition d'ordre public est sanctionné.\n\nLa preuve de l'écrit n'est pas toujours une condition de validité, mais elle est souvent une condition de preuve. Distinguer les deux évite des confusions d'examen.",
                'L\'échange d\'offre et d\'acceptation forme :',
                'Le consentement, élément de formation du contrat',
                'Uniquement une négociation sans effet',
                'Une personne morale nouvelle à chaque e-mail',
            ),
            $this->lesson(
                'L\'inexécution et la responsabilité',
                "Si le débiteur n'exécute pas, le créancier peut demander l'exécution forcée, des dommages-intérêts, parfois la résolution. La force majeure peut exonérer.\n\nLa responsabilité civile extra-contractuelle suppose une faute (ou un régime spécial), un préjudice et un lien de causalité. On ne cumule pas librement les régimes pour le même fait selon les systèmes.\n\nChiffrer le préjudice (perte subie, gain manqué) est une question de preuve autant que de droit.",
                'La force majeure peut :',
                'Exonérer le débiteur d\'une inexécution',
                'Créer automatiquement une société',
                'Remplacer le consentement des parties',
            ),
            $this->lesson(
                'Sûretés OHADA',
                "Les sûretés (gage, nantissement, hypothèque, privilèges) protègent le créancier contre le défaut de paiement. L'Acte uniforme OHADA organise publicité et rang.\n\nUne sûreté mal publiée peut être inopposable aux tiers. D'où l'importance du registre et des formalités, souvent négligées dans les cas pratiques d'étudiants.\n\nLe financier (cours de L2 Gestion) et le juriste se rejoignent ici : sans sûreté efficace, le crédit coûte plus cher.",
                'Une sûreté mal publiée risque d\'être :',
                'Inopposable aux tiers',
                'Plus forte que la Constitution',
                'Un bien public non rival',
            ),
            $this->lesson(
                'Droit des sociétés : premiers repères',
                "On distingue sociétés de personnes et de capitaux, responsabilité limitée ou illimitée. La SARL protège le patrimoine personnel des associés (sauf fautes graves, caution).\n\nLes organes (assemblée, gérance, conseil) ont des compétences propres. Un gérant qui engage la société hors objet social pose la question des pouvoirs et de l'opposabilité aux tiers.\n\nLire des statuts types n'est pas du temps perdu : c'est le contrat organisationnel de l'entreprise.",
                'Dans une SARL, la responsabilité des associés est en principe :',
                'Limitée à leurs apports',
                'Toujours illimitée sur tout leur patrimoine',
                'Inexistante même pour les dettes sociales garanties par caution',
            ),
            $this->lesson(
                'Preuve et procédure : aperçu',
                "Qui allègue doit prouver. Les modes de preuve (écrit, témoignage, aveu, présomptions) varient selon la matière et le montant. L'écrit électronique gagne du terrain.\n\nLa procédure indique les délais, le juge compétent, les voies de recours. Un bon mémoire de fond se perd si l'on saisit le mauvais tribunal trop tard.\n\nPour l'étudiant de L1, l'objectif n'est pas de devenir avoué en un chapitre, mais de respecter la rigueur : faits, règle, application, conclusion.",
                'Le principe « qui allègue doit prouver » signifie que :',
                'Celui qui avance un fait en supportant la charge de la preuve',
                'Le juge doit toujours enquête d\'office sans partie',
                'La preuve est interdite en matière commerciale',
            ),
            $this->lesson(
                'Hiérarchie des normes',
                "Une norme inférieure ne peut contredire une norme supérieure. Constitution, traités (selon les ordres), lois organiques, lois ordinaires, règlements : le contrôle de constitutionnalité et de conventionalité encadre le législateur et l'administration.\n\nUn arrêté ministériel ne peut écarter une loi. Un contrat ne peut déroger à l'ordre public légal.\n\nCette pyramide évite l'arbitraire. C'est le chapitre qui relie introduction au droit et État de droit.",
                'Un règlement administratif ne peut pas :',
                'Écarter une loi contraire à sa guise',
                'Préciser les modalités d\'application d\'une loi',
                'Exister dans un État de droit',
            ),
        ];
    }
}
