import 'package:flutter/material.dart';

import '../api/api_client.dart';
import '../models/models.dart';
import '../state/session.dart';
import 'chapter_screen.dart';

class CourseDetailScreen extends StatefulWidget {
  const CourseDetailScreen({super.key, required this.courseId});

  final int courseId;

  @override
  State<CourseDetailScreen> createState() => _CourseDetailScreenState();
}

class _CourseDetailScreenState extends State<CourseDetailScreen> {
  CourseCard? course;
  String? error;
  bool loading = true;

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
      final json = await SessionScope.of(context).api.get('/courses/${widget.courseId}');
      setState(() => course = CourseCard.fromJson(json['course'] as Map<String, dynamic>));
    } on ApiException catch (e) {
      setState(() => error = e.message);
    } finally {
      if (mounted) {
        setState(() => loading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(course?.title ?? 'Cours')),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : error != null
              ? Center(child: Text(error!))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      if (course?.description != null) Text(course!.description!),
                      const SizedBox(height: 12),
                      for (final chapter in course!.chapters)
                        Card(
                          child: ListTile(
                            leading: Icon(
                              chapter.isUnlocked ? Icons.lock_open : Icons.lock_outline,
                              color: chapter.isUnlocked ? const Color(0xFF4F46E5) : const Color(0xFF94A3B8),
                            ),
                            title: Text('${chapter.position}. ${chapter.title}'),
                            subtitle: Text(
                              chapter.completed
                                  ? 'Validé'
                                  : !chapter.isUnlocked
                                      ? 'Verrouillé'
                                      : chapter.read
                                          ? 'En cours'
                                          : 'À lire',
                            ),
                            enabled: chapter.isUnlocked,
                            onTap: chapter.isUnlocked
                                ? () async {
                                    await Navigator.of(context).push(MaterialPageRoute(
                                      builder: (_) => ChapterScreen(
                                        courseId: widget.courseId,
                                        chapterId: chapter.id,
                                      ),
                                    ));
                                    _load();
                                  }
                                : null,
                          ),
                        ),
                    ],
                  ),
                ),
    );
  }
}
