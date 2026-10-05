// This is a basic Flutter widget test.
//
// To perform an interaction with a widget in your test, use the WidgetTester
// utility in the flutter_test package. For example, you can send tap and scroll
// gestures. You can also use WidgetTester to find child widgets in the widget
// tree, read text, and verify that the values of widget properties are correct.

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:capacademia_mobile/screens/login_screen.dart';

void main() {
  testWidgets('login screen shows the centered brand and developer footer', (
    tester,
  ) async {
    tester.view.physicalSize = const Size(390, 844);
    tester.view.devicePixelRatio = 1;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    await tester.pumpWidget(const MaterialApp(home: LoginScreen()));

    final logo = find.byType(Image);
    final brandName = find.text('CapAcademia');
    expect(logo, findsOneWidget);
    expect(brandName, findsOneWidget);
    expect(
      tester.getTopLeft(brandName).dy,
      greaterThan(tester.getBottomLeft(logo).dy),
    );
    expect(find.textContaining('ML DATA'), findsOneWidget);
    expect(find.textContaining('+243982401411'), findsOneWidget);
  });
}
