import 'package:flutter/material.dart';

import '../api/api_client.dart';
import '../models/models.dart';
import '../state/session.dart';
import 'quiz_result_screen.dart';

class ChapterScreen extends StatefulWidget {
  const ChapterScreen({super.key, required this.courseId, required this.chapterId});

  final int courseId;
  final int chapterId;

  @override
  State<ChapterScreen> createState() => _ChapterScreenState();
}

class _ChapterScreenState extends State<ChapterScreen> {
  ChapterDetail? chapter;
  String? error;
  bool loading = true;
  bool saving = false;
  final Map<int, dynamic> answers = {};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _load());
  }

  Future<void> _load() async {
    setState(() {
      loading = true;
      error = null;
    });
    try {
      final json = await SessionScope.of(context).api.get(
            '/courses/${widget.courseId}/chapters/${widget.chapterId}',
          );
      setState(() => chapter = ChapterDetail.fromJson(json));
    } on ApiException catch (e) {
      setState(() => error = e.message);
    } finally {
      if (mounted) {
        setState(() => loading = false);
      }
    }
  }

  Future<void> _markRead() async {
    setState(() => saving = true);
    try {
      await SessionScope.of(context).api.post(
            '/courses/${widget.courseId}/chapters/${widget.chapterId}/read',
          );
      await _load();
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      }
    } finally {
      if (mounted) {
        setState(() => saving = false);
      }
    }
  }

  Future<void> _submitQuiz() async {
    final quiz = chapter?.quiz;
    if (quiz == null) {
      return;
    }
    setState(() => saving = true);
    try {
      final json = await SessionScope.of(context).api.post(
        '/courses/${widget.courseId}/chapters/${widget.chapterId}/quizzes/${quiz.id}',
        {
          'answers': {
            for (final question in quiz.questions) '${question.id}': answers[question.id],
          },
        },
      );
      if (!mounted) {
        return;
      }
      await Navigator.of(context).push(MaterialPageRoute(
        builder: (_) => QuizResultScreen(
          courseId: widget.courseId,
          chapterId: widget.chapterId,
          payload: json,
        ),
      ));
      await _load();
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      }
    } finally {
      if (mounted) {
        setState(() => saving = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final item = chapter;
    return Scaffold(
      appBar: AppBar(title: Text(item?.title ?? 'Chapitre')),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : error != null
              ? Center(child: Text(error!))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    Text(item!.courseTitle, style: const TextStyle(color: Color(0xFF64748B))),
                    const SizedBox(height: 12),
                    SelectableText(item.content, style: const TextStyle(height: 1.5, fontSize: 16)),
                    const SizedBox(height: 16),
                    if (!item.read)
                      FilledButton(
                        onPressed: saving ? null : _markRead,
                        child: const Text('Marquer comme lu'),
                      )
                    else
                      const Text('Chapitre lu.', style: TextStyle(color: Color(0xFF047857))),
                    if (item.quiz != null) ...[
                      const SizedBox(height: 28),
                      Text(item.quiz!.title, style: Theme.of(context).textTheme.titleLarge),
                      if (item.quiz!.description != null) Text(item.quiz!.description!),
                      Text('Seuil de réussite : ${item.quiz!.passingScore} %',
                          style: const TextStyle(color: Color(0xFF64748B))),
                      const SizedBox(height: 12),
                      for (final question in item.quiz!.questions) ...[
                        const SizedBox(height: 12),
                        Text(question.prompt, style: const TextStyle(fontWeight: FontWeight.w600)),
                        if (question.isMultipleChoice)
                          for (final option in question.answers)
                            RadioListTile<int>(
                              title: Text(option.label),
                              value: option.id,
                              groupValue: answers[question.id] as int?,
                              onChanged: (value) => setState(() => answers[question.id] = value),
                            )
                        else
                          TextField(
                            decoration: const InputDecoration(hintText: 'Votre réponse'),
                            onChanged: (value) => answers[question.id] = value,
                          ),
                      ],
                      const SizedBox(height: 16),
                      FilledButton(
                        onPressed: saving ? null : _submitQuiz,
                        child: const Text('Soumettre l’interrogation'),
                      ),
                    ],
                    if (item.nextChapter != null) ...[
                      const SizedBox(height: 16),
                      OutlinedButton(
                        onPressed: () {
                          Navigator.of(context).pushReplacement(MaterialPageRoute(
                            builder: (_) => ChapterScreen(
                              courseId: widget.courseId,
                              chapterId: item.nextChapter!.id,
                            ),
                          ));
                        },
                        child: Text('Chapitre suivant : ${item.nextChapter!.title}'),
                      ),
                    ],
                  ],
                ),
    );
  }
}
