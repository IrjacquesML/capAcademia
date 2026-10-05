import 'package:flutter/material.dart';

class Brand extends StatelessWidget {
  const Brand({
    super.key,
    this.light = false,
    this.large = false,
    this.stacked = false,
  });

  final bool light;
  final bool large;
  final bool stacked;

  @override
  Widget build(BuildContext context) {
    final logo = Image.asset(
      'assets/logo-hec-kin.jpg',
      height: large ? 72 : 40,
    );
    final name = Text(
      'CapAcademia',
      style: TextStyle(
        fontSize: large ? 22 : 16,
        fontWeight: FontWeight.w600,
        color: light ? const Color(0xFFE0E7FF) : const Color(0xFF4338CA),
      ),
    );

    if (stacked) {
      return Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [logo, const SizedBox(height: 8), name],
      );
    }

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [logo, const SizedBox(width: 10), name],
    );
  }
}
