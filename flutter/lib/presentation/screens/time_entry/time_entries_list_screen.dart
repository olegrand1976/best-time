import 'package:flutter/material.dart';
import '../../../data/services/time_entry_service.dart';
import '../../../data/models/time_entry.dart';

class TimeEntriesListScreen extends StatefulWidget {
  const TimeEntriesListScreen({super.key});

  @override
  State<TimeEntriesListScreen> createState() => _TimeEntriesListScreenState();
}

class _TimeEntriesListScreenState extends State<TimeEntriesListScreen> {
  final TimeEntryService _timeEntryService = TimeEntryService();
  List<TimeEntry> _entries = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadEntries();
  }

  Future<void> _loadEntries() async {
    setState(() => _isLoading = true);
    final result = await _timeEntryService.getTimeEntries();
    if (result['success']) {
      setState(() {
        _entries = result['entries'];
        _isLoading = false;
      });
    }
  }

  Future<void> _deleteEntry(TimeEntry entry) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Confirmer la suppression'),
        content: const Text('Voulez-vous vraiment supprimer cette entrée ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Supprimer', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );

    if (confirm == true) {
      final result = await _timeEntryService.deleteEntry(entry.id);
      if (result['success']) {
        _loadEntries();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Entrée supprimée')),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Mes temps'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadEntries,
              child: _entries.isEmpty
                  ? const Center(
                      child: Text('Aucune entrée'),
                    )
                  : ListView.builder(
                      itemCount: _entries.length,
                      itemBuilder: (context, index) {
                        final entry = _entries[index];
                        return Card(
                          margin: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 8,
                          ),
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: entry.isActive
                                  ? const Color(0xFF2563EB)
                                  : Colors.grey[300],
                              child: Icon(
                                entry.qrCodeScanned
                                    ? Icons.qr_code
                                    : Icons.access_time,
                                color: entry.isActive ? Colors.white : Colors.grey[600],
                              ),
                            ),
                            title: Text(
                              entry.projectName ?? 'Sans projet',
                              style: const TextStyle(fontWeight: FontWeight.bold),
                            ),
                            subtitle: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  '${entry.startTime.day}/${entry.startTime.month}/${entry.startTime.year} - ${entry.startTime.hour}:${entry.startTime.minute.toString().padLeft(2, '0')}',
                                ),
                                if (entry.description != null)
                                  Text(
                                    entry.description!,
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Colors.grey[600],
                                    ),
                                  ),
                                if (entry.hasLocation)
                                  Row(
                                    children: [
                                      Icon(
                                        Icons.location_on,
                                        size: 12,
                                        color: Colors.grey[600],
                                      ),
                                      const SizedBox(width: 4),
                                      Text(
                                        'Localisation capturée',
                                        style: TextStyle(
                                          fontSize: 12,
                                          color: Colors.grey[600],
                                        ),
                                      ),
                                    ],
                                  ),
                              ],
                            ),
                            trailing: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                Text(
                                  entry.durationFormatted,
                                  style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                  ),
                                ),
                                if (entry.isActive)
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 8,
                                      vertical: 2,
                                    ),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFF2563EB),
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: const Text(
                                      'En cours',
                                      style: TextStyle(
                                        color: Colors.white,
                                        fontSize: 10,
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                            onLongPress: !entry.isActive
                                ? () => _deleteEntry(entry)
                                : null,
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
