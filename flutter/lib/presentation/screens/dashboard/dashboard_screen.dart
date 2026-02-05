import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/auth_provider.dart';
import '../qr/qr_scanner_screen.dart';
import '../time_entry/clock_in_screen.dart';
import '../time_entry/time_entries_list_screen.dart';
import '../../widgets/active_timer_widget.dart';
import '../../../data/services/time_entry_service.dart';
import '../../../data/models/time_entry.dart';

class DashboardScreen extends ConsumerStatefulWidget {
  const DashboardScreen({super.key});

  @override
  ConsumerState<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends ConsumerState<DashboardScreen> {
  final TimeEntryService _timeEntryService = TimeEntryService();
  TimeEntry? _activeEntry;
  List<TimeEntry> _todayEntries = [];
  bool _isLoading = true;
  int _weeklyHours = 0;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    
    final result = await _timeEntryService.getTimeEntries();
    if (result['success']) {
      final entries = result['entries'] as List<TimeEntry>;
      
      // Find active entry
      _activeEntry = entries.firstWhere(
        (e) => e.isActive,
        orElse: () => entries.first, // fallback
      );
      if (!_activeEntry!.isActive) _activeEntry = null;
      
      // Get today's entries
      final now = DateTime.now();
      _todayEntries = entries.where((e) {
        return e.startTime.year == now.year &&
            e.startTime.month == now.month &&
            e.startTime.day == now.day;
      }).toList();
      
      // Calculate weekly hours
      final weekStart = now.subtract(Duration(days: now.weekday - 1));
      final weeklyEntries = entries.where((e) {
        return e.startTime.isAfter(weekStart) && e.duration != null;
      });
      _weeklyHours = weeklyEntries.fold(0, (sum, e) => sum + (e.duration ?? 0));
    }
    
    setState(() => _isLoading = false);
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authNotifierProvider);
    final user = authState.value;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Best Time'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadData,
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await ref.read(authNotifierProvider.notifier).logout();
            },
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Welcome Card
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Bonjour, ${user?.name ?? ""}',
                            style: Theme.of(context).textTheme.headlineSmall,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Rôle: ${user?.role ?? ""}',
                            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                                  color: Colors.grey[600],
                                ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Active Timer
                  if (_activeEntry != null)
                    ActiveTimerWidget(
                      entry: _activeEntry!,
                      onStop: () async {
                        await _timeEntryService.clockOut();
                        _loadData();
                      },
                    ),

                  // Weekly Summary
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Cette semaine',
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              const Icon(Icons.access_time, size: 32, color: Color(0xFF2563EB)),
                              const SizedBox(width: 12),
                              Text(
                                '${(_weeklyHours / 3600).toStringAsFixed(1)} heures',
                                style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                                      fontWeight: FontWeight.bold,
                                      color: const Color(0xFF2563EB),
                                    ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Quick Actions
                  Text(
                    'Actions rapides',
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: _activeEntry == null
                              ? () async {
                                  await Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (context) => const ClockInScreen(),
                                    ),
                                  );
                                  _loadData();
                                }
                              : null,
                          icon: const Icon(Icons.play_arrow),
                          label: const Text('Pointer'),
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: _activeEntry == null
                              ? () async {
                                  await Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (context) => const QRScannerScreen(),
                                    ),
                                  );
                                  _loadData();
                                }
                              : null,
                          icon: const Icon(Icons.qr_code_scanner),
                          label: const Text('Scanner QR'),
                          style: ElevatedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            backgroundColor: const Color(0xFF10B981),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Today's Entries
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        "Aujourd'hui",
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      TextButton(
                        onPressed: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (context) => const TimeEntriesListScreen(),
                            ),
                          );
                        },
                        child: const Text('Voir tout'),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  if (_todayEntries.isEmpty)
                    const Card(
                      child: Padding(
                        padding: EdgeInsets.all(16),
                        child: Text('Aucune entrée aujourd\'hui'),
                      ),
                    )
                  else
                    ..._todayEntries.map((entry) => Card(
                          child: ListTile(
                            leading: Icon(
                              entry.qrCodeScanned ? Icons.qr_code : Icons.access_time,
                              color: const Color(0xFF2563EB),
                            ),
                            title: Text(entry.projectName ?? 'Sans projet'),
                            subtitle: Text(
                              '${entry.startTime.hour}:${entry.startTime.minute.toString().padLeft(2, '0')} - ${entry.endTime != null ? '${entry.endTime!.hour}:${entry.endTime!.minute.toString().padLeft(2, '0')}' : 'En cours'}',
                            ),
                            trailing: Text(
                              entry.durationFormatted,
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                              ),
                            ),
                          ),
                        )),
                ],
              ),
            ),
    );
  }
}
