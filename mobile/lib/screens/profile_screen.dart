import 'package:flutter/material.dart';

import '../state/session.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final session = SessionScope.of(context);
    final user = session.user;
    if (user == null) {
      return const SizedBox.shrink();
    }
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Text('Profil', style: Theme.of(context).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w700)),
        const SizedBox(height: 16),
        Card(
          child: ListTile(
            title: Text(user.name),
            subtitle: Text('${user.email}\n${user.roleLabel}\n${user.academicLine}'),
            isThreeLine: true,
          ),
        ),
        if (user.role != 'student')
          const Padding(
            padding: EdgeInsets.only(top: 12),
            child: Text('L’administration se fait sur le site web CapAcademia.'),
          ),
        const SizedBox(height: 24),
        OutlinedButton(
          onPressed: session.logout,
          child: const Text('Déconnexion'),
        ),
      ],
    );
  }
}
