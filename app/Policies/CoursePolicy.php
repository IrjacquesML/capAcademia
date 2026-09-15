<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * 404 plutôt que 403 est appliqué dans le contrôleur pour ne pas révéler
     * l'existence d'un cours d'une autre faculté / option / promotion.
     */
    public function view(User $user, Course $course): bool
    {
        if (! $user->canAccessCourse($course)) {
            return false;
        }

        if ($user->isStudent() && ! $course->is_published) {
            return false;
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->isPrivileged();
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isPrivileged() && $user->canAccessCourse($course);
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->update($user, $course);
    }
}
