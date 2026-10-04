class NamedRef {
  const NamedRef({required this.id, required this.name});

  final int id;
  final String name;

  factory NamedRef.fromJson(Map<String, dynamic>? json) {
    if (json == null) {
      return const NamedRef(id: 0, name: '');
    }
    return NamedRef(id: json['id'] as int? ?? 0, name: json['name']?.toString() ?? '');
  }
}

class UserAccount {
  const UserAccount({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.roleLabel,
    this.faculty,
    this.option,
    this.promotion,
  });

  final int id;
  final String name;
  final String email;
  final String role;
  final String roleLabel;
  final NamedRef? faculty;
  final NamedRef? option;
  final NamedRef? promotion;

  String get academicLine {
    final parts = [
      faculty?.name,
      option?.name,
      promotion?.name,
    ].where((part) => part != null && part.isNotEmpty).join(' · ');
    return parts.isEmpty ? 'Parcours non renseigné' : parts;
  }

  factory UserAccount.fromJson(Map<String, dynamic> json) {
    return UserAccount(
      id: json['id'] as int,
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      role: json['role']?.toString() ?? '',
      roleLabel: json['role_label']?.toString() ?? '',
      faculty: json['faculty'] is Map<String, dynamic>
          ? NamedRef.fromJson(json['faculty'] as Map<String, dynamic>)
          : null,
      option: json['option'] is Map<String, dynamic>
          ? NamedRef.fromJson(json['option'] as Map<String, dynamic>)
          : null,
      promotion: json['promotion'] is Map<String, dynamic>
          ? NamedRef.fromJson(json['promotion'] as Map<String, dynamic>)
          : null,
    );
  }
}

class CourseCard {
  const CourseCard({
    required this.id,
    required this.title,
    this.description,
    this.faculty,
    this.option,
    this.promotion,
    this.progressDone = 0,
    this.progressTotal = 0,
    this.progressPercent = 0,
    this.resumeChapter,
    this.chapters = const [],
  });

  final int id;
  final String title;
  final String? description;
  final String? faculty;
  final String? option;
  final String? promotion;
  final int progressDone;
  final int progressTotal;
  final int progressPercent;
  final ChapterRef? resumeChapter;
  final List<ChapterSummary> chapters;

  factory CourseCard.fromJson(Map<String, dynamic> json) {
    return CourseCard(
      id: json['id'] as int,
      title: json['title']?.toString() ?? '',
      description: json['description']?.toString(),
      faculty: json['faculty']?.toString(),
      option: json['option']?.toString(),
      promotion: json['promotion']?.toString(),
      progressDone: json['progress_done'] as int? ?? 0,
      progressTotal: json['progress_total'] as int? ?? 0,
      progressPercent: json['progress_percent'] as int? ?? 0,
      resumeChapter: json['resume_chapter'] is Map<String, dynamic>
          ? ChapterRef.fromJson(json['resume_chapter'] as Map<String, dynamic>)
          : null,
      chapters: [
        for (final item in (json['chapters'] as List? ?? []))
          if (item is Map<String, dynamic>) ChapterSummary.fromJson(item),
      ],
    );
  }
}

class ChapterRef {
  const ChapterRef({required this.id, required this.title, this.position = 0});

  final int id;
  final String title;
  final int position;

  factory ChapterRef.fromJson(Map<String, dynamic> json) {
    return ChapterRef(
      id: json['id'] as int,
      title: json['title']?.toString() ?? '',
      position: json['position'] as int? ?? 0,
    );
  }
}

class ChapterSummary {
  const ChapterSummary({
    required this.id,
    required this.title,
    required this.position,
    required this.isUnlocked,
    required this.hasQuiz,
    this.quizId,
    required this.read,
    required this.completed,
    required this.quizSubmitted,
  });

  final int id;
  final String title;
  final int position;
  final bool isUnlocked;
  final bool hasQuiz;
  final int? quizId;
  final bool read;
  final bool completed;
  final bool quizSubmitted;

  factory ChapterSummary.fromJson(Map<String, dynamic> json) {
    return ChapterSummary(
      id: json['id'] as int,
      title: json['title']?.toString() ?? '',
      position: json['position'] as int? ?? 0,
      isUnlocked: json['is_unlocked'] == true,
      hasQuiz: json['has_quiz'] == true,
      quizId: json['quiz_id'] as int?,
      read: json['read'] == true,
      completed: json['completed'] == true,
      quizSubmitted: json['quiz_submitted'] == true,
    );
  }
}

class AnswerOption {
  const AnswerOption({required this.id, required this.label});

  final int id;
  final String label;

  factory AnswerOption.fromJson(Map<String, dynamic> json) {
    return AnswerOption(id: json['id'] as int, label: json['label']?.toString() ?? '');
  }
}

class QuizQuestion {
  const QuizQuestion({
    required this.id,
    required this.type,
    required this.prompt,
    required this.points,
    required this.answers,
  });

  final int id;
  final String type;
  final String prompt;
  final int points;
  final List<AnswerOption> answers;

  bool get isMultipleChoice => type == 'multiple_choice';

  factory QuizQuestion.fromJson(Map<String, dynamic> json) {
    return QuizQuestion(
      id: json['id'] as int,
      type: json['type']?.toString() ?? '',
      prompt: json['prompt']?.toString() ?? '',
      points: json['points'] as int? ?? 1,
      answers: [
        for (final item in (json['answers'] as List? ?? []))
          if (item is Map<String, dynamic>) AnswerOption.fromJson(item),
      ],
    );
  }
}

class QuizPayload {
  const QuizPayload({
    required this.id,
    required this.title,
    this.description,
    required this.passingScore,
    required this.questions,
  });

  final int id;
  final String title;
  final String? description;
  final int passingScore;
  final List<QuizQuestion> questions;

  factory QuizPayload.fromJson(Map<String, dynamic> json) {
    return QuizPayload(
      id: json['id'] as int,
      title: json['title']?.toString() ?? '',
      description: json['description']?.toString(),
      passingScore: json['passing_score'] as int? ?? 50,
      questions: [
        for (final item in (json['questions'] as List? ?? []))
          if (item is Map<String, dynamic>) QuizQuestion.fromJson(item),
      ],
    );
  }
}

class QuizAttempt {
  const QuizAttempt({
    required this.id,
    required this.score,
    required this.maxScore,
    required this.percentage,
    required this.passed,
    required this.breakdown,
    this.submittedAt,
  });

  final int id;
  final int score;
  final int maxScore;
  final double percentage;
  final bool passed;
  final List<Map<String, dynamic>> breakdown;
  final String? submittedAt;

  factory QuizAttempt.fromJson(Map<String, dynamic> json) {
    return QuizAttempt(
      id: json['id'] as int,
      score: json['score'] as int? ?? 0,
      maxScore: json['max_score'] as int? ?? 0,
      percentage: (json['percentage'] as num?)?.toDouble() ?? 0,
      passed: json['passed'] == true,
      breakdown: [
        for (final item in (json['breakdown'] as List? ?? []))
          if (item is Map<String, dynamic>) item,
      ],
      submittedAt: json['submitted_at']?.toString(),
    );
  }
}

class ChapterDetail {
  const ChapterDetail({
    required this.courseId,
    required this.courseTitle,
    required this.id,
    required this.title,
    required this.position,
    required this.content,
    required this.read,
    required this.completed,
    this.quiz,
    this.latestAttempt,
    this.nextChapter,
  });

  final int courseId;
  final String courseTitle;
  final int id;
  final String title;
  final int position;
  final String content;
  final bool read;
  final bool completed;
  final QuizPayload? quiz;
  final QuizAttempt? latestAttempt;
  final ChapterRef? nextChapter;

  factory ChapterDetail.fromJson(Map<String, dynamic> json) {
    final course = json['course'] as Map<String, dynamic>? ?? {};
    final chapter = json['chapter'] as Map<String, dynamic>? ?? {};
    return ChapterDetail(
      courseId: course['id'] as int? ?? 0,
      courseTitle: course['title']?.toString() ?? '',
      id: chapter['id'] as int? ?? 0,
      title: chapter['title']?.toString() ?? '',
      position: chapter['position'] as int? ?? 0,
      content: chapter['content']?.toString() ?? '',
      read: chapter['read'] == true,
      completed: chapter['completed'] == true,
      quiz: chapter['quiz'] is Map<String, dynamic>
          ? QuizPayload.fromJson(chapter['quiz'] as Map<String, dynamic>)
          : null,
      latestAttempt: chapter['latest_attempt'] is Map<String, dynamic>
          ? QuizAttempt.fromJson(chapter['latest_attempt'] as Map<String, dynamic>)
          : null,
      nextChapter: json['next_chapter'] is Map<String, dynamic>
          ? ChapterRef.fromJson(json['next_chapter'] as Map<String, dynamic>)
          : null,
    );
  }
}

class DashboardData {
  const DashboardData({
    required this.user,
    required this.courses,
    required this.attemptCount,
    required this.chaptersDone,
    required this.chaptersTotal,
    this.resumeCourseId,
    this.resumeChapter,
  });

  final UserAccount user;
  final List<CourseCard> courses;
  final int attemptCount;
  final int chaptersDone;
  final int chaptersTotal;
  final int? resumeCourseId;
  final ChapterRef? resumeChapter;

  factory DashboardData.fromJson(Map<String, dynamic> json) {
    final resume = json['resume'] as Map<String, dynamic>?;
    final resumeCourse = resume?['course'] as Map<String, dynamic>?;
    return DashboardData(
      user: UserAccount.fromJson(json['user'] as Map<String, dynamic>),
      courses: [
        for (final item in (json['courses'] as List? ?? []))
          if (item is Map<String, dynamic>) CourseCard.fromJson(item),
      ],
      attemptCount: json['attempt_count'] as int? ?? 0,
      chaptersDone: json['chapters_done'] as int? ?? 0,
      chaptersTotal: json['chapters_total'] as int? ?? 0,
      resumeCourseId: resumeCourse?['id'] as int?,
      resumeChapter: resume?['chapter'] is Map<String, dynamic>
          ? ChapterRef.fromJson(resume!['chapter'] as Map<String, dynamic>)
          : null,
    );
  }
}
