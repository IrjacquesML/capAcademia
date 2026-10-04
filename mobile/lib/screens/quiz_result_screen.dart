import 'package:flutter/material.dart';

import '../models/models.dart';
import 'chapter_screen.dart';

class QuizResultScreen extends StatelessWidget {
  const QuizResultScreen({
    super.key,
    required this.courseId,
    required this.chapterId,
    required this.payload,
  });

  final int courseId;
  final int chapterId;
  final Map<String, dynamic> payload;

  @override
  Widget build(BuildContext context) {
    final attempt = QuizAttempt.fromJson(payload['attempt'] as Map<String, dynamic>);
    final next = payload['next_chapter'] is Map<String, dynamic>
        ? ChapterRef.fromJson(payload['next_chapter'] as Map<String, dynamic>)
        : null;
    return Scaffold(
      appBar: AppBar(title: const Text('Corrigé')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            color: attempt.passed ? const Color(0xFFECFDF5) : const Color(0xFFFFFBEB),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    attempt.passed ? 'Interrogation réussie' : 'Interrogation non validée',
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      fontSize: 18,
                      color: attempt.passed ? const Color(0xFF065F46) : const Color(0xFF92400E),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text('Score : ${attempt.score} / ${attempt.maxScore} (${attempt.percentage} %)'),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          for (var i = 0; i < attempt.breakdown.length; i++)
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Question ${i + 1} — ${attempt.breakdown[i]['prompt'] ?? ''}',
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      (attempt.breakdown[i]['correct'] == true) ? 'Correct' : 'Incorrect',
                      style: TextStyle(
                        color: attempt.breakdown[i]['correct'] == true
                            ? const Color(0xFF047857)
                            : const Color(0xFFB91C1C),
                      ),
                    ),
                    Text('Votre réponse : ${attempt.breakdown[i]['student_answer'] ?? 'Non répondu'}'),
                    Text(
                      'Bonne réponse : ${((attempt.breakdown[i]['expected_answers'] as List?) ?? []).join(' · ')}',
                    ),
                  ],
                ),
              ),
            ),
          const SizedBox(height: 16),
          OutlinedButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Retour au chapitre'),
          ),
          if (next != null)
            FilledButton(
              onPressed: () {
                Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(
                    builder: (_) => ChapterScreen(courseId: courseId, chapterId: next.id),
                  ),
                  (route) => route.isFirst,
                );
              },
              child: Text('Chapitre suivant : ${next.title}'),
            ),
        ],
      ),
    );
  }
}
