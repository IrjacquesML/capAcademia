import 'package:flutter/material.dart';

import '../api/api_client.dart';
import '../models/models.dart';
import '../state/session.dart';
import 'course_detail_screen.dart';

class CoursesScreen extends StatefulWidget {
  const CoursesScreen({super.key});

  @override
  State<CoursesScreen> createState() => _CoursesScreenState();
}

class _CoursesScreenState extends State<CoursesScreen> {
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
      final json = await SessionScope.of(context).api.get('/courses');
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
          Text('Mes cours', style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w700)),
          const SizedBox(height: 12),
          if (error != null) Text(error!, style: const TextStyle(color: Color(0xFFB91C1C))),
          for (final course in courses)
            Card(
              child: ListTile(
                title: Text(course.title),
                subtitle: Text([
                  course.faculty,
                  course.option,
                  course.promotion,
                ].whereType<String>().where((v) => v.isNotEmpty).join(' · ')),
                trailing: const Icon(Icons.chevron_right),
                onTap: () {
                  Navigator.of(context).push(MaterialPageRoute(
                    builder: (_) => CourseDetailScreen(courseId: course.id),
                  ));
                },
              ),
            ),
          if (courses.isEmpty)
            const Padding(
              padding: EdgeInsets.only(top: 32),
              child: Text('Aucun cours n’est disponible pour votre parcours.'),
            ),
        ],
      ),
    );
  }
}
