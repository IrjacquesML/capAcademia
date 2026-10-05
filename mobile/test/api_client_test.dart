import 'package:flutter_test/flutter_test.dart';
import 'package:capacademia_mobile/api/api_client.dart';

void main() {
  test('uses the production domain by default', () {
    expect(ApiClient().baseUrl, 'https://www.capacademia.net');
  });
}
