import 'package:flutter/material.dart';

class Brand extends StatelessWidget {
  const Brand({super.key, this.light = false, this.large = false});

  final bool light;
  final bool large;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Image.asset(
          'assets/logo-hec-kin.jpg',
          height: large ? 72 : 40,
        ),
        const SizedBox(width: 10),
        Text(
          'CapAcademia',
          style: TextStyle(
            fontSize: large ? 22 : 16,
            fontWeight: FontWeight.w600,
            color: light ? const Color(0xFFE0E7FF) : const Color(0xFF4338CA),
          ),
        ),
      ],
    );
  }
}
