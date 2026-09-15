<?php

namespace App\Enums;

enum AuditAction: string
{
    case Login = 'login';
    case LoginFailed = 'login_failed';
    case Logout = 'logout';
    case DashboardViewed = 'dashboard_viewed';
    case CourseListViewed = 'course_list_viewed';
    case CourseViewed = 'course_viewed';
    case ChapterViewed = 'chapter_viewed';
    case ChapterRead = 'chapter_read';
    case ProgressViewed = 'progress_viewed';
    case ProfileViewed = 'profile_viewed';
    case QuizResultViewed = 'quiz_result_viewed';
    case QuizSubmitted = 'quiz_submitted';
    case UserCreated = 'user_created';
    case UserUpdated = 'user_updated';
    case UserDeleted = 'user_deleted';

    public function label(): string
    {
        return match ($this) {
            self::Login => 'Connexion',
            self::LoginFailed => 'Échec de connexion',
            self::Logout => 'Déconnexion',
            self::DashboardViewed => 'Accueil consulté',
            self::CourseListViewed => 'Liste des cours consultée',
            self::CourseViewed => 'Cours ouvert',
            self::ChapterViewed => 'Chapitre ouvert',
            self::ChapterRead => 'Chapitre lu',
            self::ProgressViewed => 'Progression consultée',
            self::ProfileViewed => 'Profil consulté',
            self::QuizResultViewed => 'Corrigé consulté',
            self::QuizSubmitted => 'Interrogation soumise',
            self::UserCreated => 'Compte créé',
            self::UserUpdated => 'Compte modifié',
            self::UserDeleted => 'Compte supprimé',
        };
    }

    public function notifiesAdmins(): bool
    {
        return match ($this) {
            self::ChapterRead, self::QuizSubmitted => true,
            default => false,
        };
    }

    public function isRepeatableView(): bool
    {
        return match ($this) {
            self::DashboardViewed,
            self::CourseListViewed,
            self::CourseViewed,
            self::ChapterViewed,
            self::ProgressViewed,
            self::ProfileViewed,
            self::QuizResultViewed => true,
            default => false,
        };
    }
}
