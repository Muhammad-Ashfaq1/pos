import 'package:flutter/material.dart';
import 'package:pos_mobile/app/app.dart';
import 'package:pos_mobile/core/app_services.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await AppServices.session.load();
  runApp(const PosApp());
}
