import 'package:flutter/material.dart';

import 'screens/login_screen.dart';
import 'state/session.dart';
import 'widgets/shell.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final session = SessionController();
  await session.restore();
  runApp(CapAcademiaApp(session: session));
}

class CapAcademiaApp extends StatelessWidget {
  const CapAcademiaApp({super.key, required this.session});

  final SessionController session;

  @override
  Widget build(BuildContext context) {
    return SessionScope(
      controller: session,
      child: ListenableBuilder(
        listenable: session,
        builder: (context, _) {
          return MaterialApp(
            title: 'CapAcademia',
            debugShowCheckedModeBanner: false,
            theme: ThemeData(
              colorScheme: ColorScheme.fromSeed(
                seedColor: const Color(0xFF4F46E5),
              ),
              useMaterial3: true,
              inputDecorationTheme: const InputDecorationTheme(
                border: OutlineInputBorder(),
              ),
            ),
            home: !session.ready
                ? const Scaffold(
                    body: Center(child: CircularProgressIndicator()),
                  )
                : session.user == null
                ? const LoginScreen()
                : const StudentShell(),
          );
        },
      ),
    );
  }
}
