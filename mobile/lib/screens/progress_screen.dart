import 'package:flutter/material.dart';

import '../api/api_client.dart';
import '../models/models.dart';
import '../state/session.dart';
import 'course_detail_screen.dart';

class ProgressScreen extends StatefulWidget {
  const ProgressScreen({super.key});

  @override
  State<ProgressScreen> createState() => _ProgressScreenState();
}

class _ProgressScreenState extends State<ProgressScreen> {
  List<CourseCard> courses = [];
  bool loading = true;
  String? error;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (loading && courses.isEmpty) {
      _load();
    }
  }

  Future<void> _load() async {
    setState(() {
      loading = true;
      error = null;
    });
    try {
      final json = await SessionScope.of(context).api.get('/progress');
      setState(() {
        courses = [
          for (final item in (json['courses'] as List? ?? []))
            if (item is Map<String, dynamic>) CourseCard.fromJson(item),
        ];
      });
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
    if (loading && courses.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }
    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text('Progression', style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w700)),
          const SizedBox(height: 12),
          if (error != null) Text(error!),
          for (final course in courses)
            Card(
              child: ListTile(
                title: Text(course.title),
                subtitle: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const SizedBox(height: 8),
                    LinearProgressIndicator(value: course.progressPercent / 100),
                    const SizedBox(height: 6),
                    Text('${course.progressPercent} % · ${course.progressDone}/${course.progressTotal}'),
                  ],
                ),
                onTap: () {
                  Navigator.of(context).push(MaterialPageRoute(
                    builder: (_) => CourseDetailScreen(courseId: course.id),
                  ));
                },
              ),
            ),
        ],
      ),
    );
  }
}
