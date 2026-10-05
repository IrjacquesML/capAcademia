import 'package:flutter/material.dart';

import '../api/api_client.dart';
import '../state/session.dart';
import '../widgets/brand.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final email = TextEditingController();
  final password = TextEditingController();
  bool loading = false;
  String? error;

  @override
  void dispose() {
    email.dispose();
    password.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() {
      loading = true;
      error = null;
    });
    try {
      await SessionScope.of(context).login(email.text.trim(), password.text);
    } on ApiException catch (e) {
      setState(() => error = e.message);
    } catch (_) {
      setState(
        () => error = 'Impossible de joindre le serveur. Vérifiez l’URL API.',
      );
    } finally {
      if (mounted) {
        setState(() => loading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Row(
        children: [
          if (MediaQuery.sizeOf(context).width >= 800)
            Expanded(
              child: Container(
                color: const Color(0xFF312E81),
                padding: const EdgeInsets.all(40),
                child: const Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Brand(light: true, large: true),
                    SizedBox(height: 16),
                    Text(
                      'Cours, chapitres et interrogations de votre faculté, option et promotion.',
                      style: TextStyle(
                        color: Color(0xFFC7D2FE),
                        fontSize: 16,
                        height: 1.4,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          Expanded(
            child: Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 420),
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      if (MediaQuery.sizeOf(context).width < 800) ...[
                        const Brand(large: true, stacked: true),
                        const SizedBox(height: 24),
                      ],
                      Text(
                        'Connexion',
                        style: Theme.of(context).textTheme.headlineSmall
                            ?.copyWith(fontWeight: FontWeight.w700),
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'Utilisez le compte CapAcademia de votre établissement.',
                      ),
                      const SizedBox(height: 24),
                      TextField(
                        controller: email,
                        keyboardType: TextInputType.emailAddress,
                        autocorrect: false,
                        decoration: const InputDecoration(labelText: 'E-mail'),
                      ),
                      const SizedBox(height: 12),
                      TextField(
                        controller: password,
                        obscureText: true,
                        decoration: const InputDecoration(
                          labelText: 'Mot de passe',
                        ),
                        onSubmitted: (_) => _submit(),
                      ),
                      if (error != null) ...[
                        const SizedBox(height: 12),
                        Text(
                          error!,
                          style: const TextStyle(color: Color(0xFFB91C1C)),
                        ),
                      ],
                      const SizedBox(height: 20),
                      FilledButton(
                        onPressed: loading ? null : _submit,
                        child: Text(loading ? 'Connexion…' : 'Se connecter'),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
      bottomNavigationBar: SafeArea(
        top: false,
        minimum: const EdgeInsets.fromLTRB(16, 8, 16, 12),
        child: const Text(
          'Plateforme développée par ML DATA : +243982401411',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
        ),
      ),
    );
  }
}
