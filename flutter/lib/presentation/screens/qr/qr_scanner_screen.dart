import 'package:flutter/material.dart';
import 'package:qr_code_scanner/qr_code_scanner.dart';
import '../../../data/services/qr_code_service.dart';
import '../../../data/services/time_entry_service.dart';
import '../../../data/services/location_service.dart';
import '../../../data/models/project.dart';

class QRScannerScreen extends StatefulWidget {
  const QRScannerScreen({super.key});

  @override
  State<QRScannerScreen> createState() => _QRScannerScreenState();
}

class _QRScannerScreenState extends State<QRScannerScreen> {
  final GlobalKey qrKey = GlobalKey(debugLabel: 'QR');
  QRViewController? controller;
  final QRCodeService _qrService = QRCodeService();
  final TimeEntryService _timeEntryService = TimeEntryService();
  final LocationService _locationService = LocationService();
  bool _isProcessing = false;

  @override
  void dispose() {
    controller?.dispose();
    super.dispose();
  }

  void _onQRViewCreated(QRViewController controller) {
    this.controller = controller;
    controller.scannedDataStream.listen((scanData) {
      if (!_isProcessing && scanData.code != null) {
        _handleQRCode(scanData.code!);
      }
    });
  }

  Future<void> _handleQRCode(String qrData) async {
    setState(() => _isProcessing = true);
    controller?.pauseCamera();

    // Validate QR code
    final qrResult = await _qrService.validateQRCode(qrData);
    
    if (!qrResult['success']) {
      if (!mounted) return;
      _showError(qrResult['message']);
      setState(() => _isProcessing = false);
      controller?.resumeCamera();
      return;
    }

    final project = qrResult['project'] as Project;

    // Get location
    final locationResult = await _locationService.getCurrentLocation();
    double? latitude;
    double? longitude;
    double? accuracy;

    if (locationResult['success']) {
      latitude = locationResult['latitude'];
      longitude = locationResult['longitude'];
      accuracy = locationResult['accuracy'];

      // Check geofencing if project has location
      if (project.hasGeofence) {
        final isWithin = _locationService.isWithinGeofence(
          userLat: latitude!,
          userLon: longitude!,
          targetLat: project.latitude!,
          targetLon: project.longitude!,
          radiusMeters: project.geofenceRadius!.toDouble(),
        );

        if (!isWithin) {
          if (!mounted) return;
          _showError('Vous êtes trop loin du site de travail');
          setState(() => _isProcessing = false);
          controller?.resumeCamera();
          return;
        }
      }
    }

    // Clock in
    final clockInResult = await _timeEntryService.clockIn(
      projectId: project.id,
      description: 'Pointage via QR code - ${project.name}',
      latitude: latitude,
      longitude: longitude,
      locationAccuracy: accuracy,
      qrCodeScanned: true,
    );

    if (!mounted) return;

    if (clockInResult['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Pointage réussi sur ${project.name}'),
          backgroundColor: Colors.green,
        ),
      );
      Navigator.pop(context);
    } else {
      _showError(clockInResult['message']);
      setState(() => _isProcessing = false);
      controller?.resumeCamera();
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Scanner QR Code'),
      ),
      body: Column(
        children: [
          Expanded(
            flex: 5,
            child: QRView(
              key: qrKey,
              onQRViewCreated: _onQRViewCreated,
              overlay: QrScannerOverlayShape(
                borderColor: const Color(0xFF2563EB),
                borderRadius: 10,
                borderLength: 30,
                borderWidth: 10,
                cutOutSize: 300,
              ),
            ),
          ),
          Expanded(
            flex: 1,
            child: Container(
              color: Colors.black87,
              child: Center(
                child: _isProcessing
                    ? const Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          CircularProgressIndicator(color: Colors.white),
                          SizedBox(height: 16),
                          Text(
                            'Traitement en cours...',
                            style: TextStyle(color: Colors.white),
                          ),
                        ],
                      )
                    : const Text(
                        'Placez le QR code dans le cadre',
                        style: TextStyle(color: Colors.white, fontSize: 16),
                      ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
