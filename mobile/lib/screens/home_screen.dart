import 'package:flutter/material.dart';

import '../api/api_client.dart';
import '../models/models.dart';
import '../state/session.dart';
import 'chapter_screen.dart';
import 'course_detail_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  DashboardData? data;
  String? error;
  bool loading = true;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (loading && data == null) {
      _load();
    }
  }

  Future<void> _load() async {
    setState(() {
      loading = true;
      error = null;
    });
    try {
      final json = await SessionScope.of(context).api.get('/dashboard');
      setState(() => data = DashboardData.fromJson(json));
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
    if (loading && data == null) {
      return const Center(child: CircularProgressIndicator());
    }
    if (error != null && data == null) {
      return Center(child: Text(error!));
    }
    final dashboard = data!;
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text('Bonjour ${dashboard.user.name}', style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w700)),
          const SizedBox(height: 4),
          Text(dashboard.user.academicLine, style: const TextStyle(color: Color(0xFF475569))),
          const SizedBox(height: 16),
          Row(
            children: [
              _Stat(label: 'Chapitres', value: '${dashboard.chaptersDone}/${dashboard.chaptersTotal}'),
              const SizedBox(width: 12),
              _Stat(label: 'Interrogations', value: '${dashboard.attemptCount}'),
            ],
          ),
          if (dashboard.resumeChapter != null && dashboard.resumeCourseId != null) ...[
            const SizedBox(height: 20),
            Card(
              child: ListTile(
                title: const Text('Reprendre'),
                subtitle: Text(dashboard.resumeChapter!.title),
                trailing: const Icon(Icons.chevron_right),
                onTap: () {
                  Navigator.of(context).push(MaterialPageRoute(
                    builder: (_) => ChapterScreen(
                      courseId: dashboard.resumeCourseId!,
                      chapterId: dashboard.resumeChapter!.id,
                    ),
                  ));
                },
              ),
            ),
          ],
          const SizedBox(height: 20),
          Text('Vos cours', style: Theme.of(context).textTheme.titleMedium),
          const SizedBox(height: 8),
          for (final course in dashboard.courses)
            Card(
              child: ListTile(
                title: Text(course.title),
                subtitle: Text('${course.progressPercent} % · ${course.progressDone}/${course.progressTotal} chapitres'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () {
                  Navigator.of(context).push(MaterialPageRoute(
                    builder: (_) => CourseDetailScreen(courseId: course.id),
                  ));
                },
              ),
            ),
          if (dashboard.courses.isEmpty)
            const Padding(
              padding: EdgeInsets.only(top: 24),
              child: Text('Aucun cours n’est disponible pour votre faculté, option et promotion.'),
            ),
        ],
      ),
    );
  }
}

class _Stat extends StatelessWidget {
  const _Stat({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(value, style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w700)),
              Text(label, style: const TextStyle(color: Color(0xFF64748B))),
            ],
          ),
        ),
      ),
    );
  }
}
