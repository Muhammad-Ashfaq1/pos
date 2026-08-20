import 'package:pos_mobile/features/auth/models/customer.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AuthSession {
  static const _tokenKey = 'customer_token';
  static const _shopKey = 'shop_slug';

  String? token;
  String? shopSlug;
  Customer? customer;

  bool get isAuthenticated => token != null && token!.isNotEmpty;

  Future<void> load() async {
    final prefs = await SharedPreferences.getInstance();
    token = prefs.getString(_tokenKey);
    shopSlug = prefs.getString(_shopKey);
  }

  Future<void> save({
    required String token,
    required String shopSlug,
    required Customer customer,
  }) async {
    this.token = token;
    this.shopSlug = shopSlug;
    this.customer = customer;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
    await prefs.setString(_shopKey, shopSlug);
  }

  Future<void> rememberShop(String shopSlug) async {
    this.shopSlug = shopSlug;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_shopKey, shopSlug);
  }

  Future<void> clearToken() async {
    token = null;
    customer = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
  }
}
