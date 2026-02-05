import 'package:flutter/material.dart';
import '../../../data/services/time_entry_service.dart';
import '../../../data/services/location_service.dart';
import '../../../data/services/project_service.dart';
import '../../../data/models/project.dart';

class ClockInScreen extends StatefulWidget {
  const ClockInScreen({super.key});

  @override
  State<ClockInScreen> createState() => _ClockInScreenState();
}

class _ClockInScreenState extends State<ClockInScreen> {
  final TimeEntryService _timeEntryService = TimeEntryService();
  final LocationService _locationService = LocationService();
  final ProjectService _projectService = ProjectService();
  final TextEditingController _descriptionController = TextEditingController();
  
  List<Project> _projects = [];
  Project? _selectedProject;
  bool _isLoading = false;
  bool _isLoadingProjects = true;
  Map<String, dynamic>? _location;

  @override
  void initState() {
    super.initState();
    _loadProjects();
    _getLocation();
  }

  @override
  void dispose() {
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _loadProjects() async {
    final result = await _projectService.getProjects();
    if (result['success']) {
      setState(() {
        _projects = result['projects'];
        _isLoadingProjects = false;
      });
    }
  }

  Future<void> _getLocation() async {
    final result = await _locationService.getCurrentLocation();
    if (result['success']) {
      setState(() => _location = result);
    }
  }

  Future<void> _handleClockIn() async {
    setState(() => _isLoading = true);

    final result = await _timeEntryService.clockIn(
      projectId: _selectedProject?.id,
      description: _descriptionController.text.trim(),
      latitude: _location?['latitude'],
      longitude: _location?['longitude'],
      locationAccuracy: _location?['accuracy'],
    );

    setState(() => _isLoading = false);

    if (!mounted) return;

    if (result['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pointage réussi'),
          backgroundColor: Colors.green,
        ),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Échec du pointage'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Pointer'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Project Selection
            if (_isLoadingProjects)
              const Center(child: CircularProgressIndicator())
            else
              DropdownButtonFormField<Project>(
                value: _selectedProject,
                decoration: const InputDecoration(
                  labelText: 'Projet (optionnel)',
                  prefixIcon: Icon(Icons.work_outline),
                ),
                items: _projects.map((project) {
                  return DropdownMenuItem(
                    value: project,
                    child: Text(project.name),
                  );
                }).toList(),
                onChanged: (value) {
                  setState(() => _selectedProject = value);
                },
              ),
            const SizedBox(height: 16),

            // Description
            TextField(
              controller: _descriptionController,
              decoration: const InputDecoration(
                labelText: 'Description (optionnel)',
                prefixIcon: Icon(Icons.description_outlined),
                hintText: 'Que faites-vous ?',
              ),
              maxLines: 3,
            ),
            const SizedBox(height: 16),

            // Location Status
            Card(
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: Row(
                  children: [
                    Icon(
                      _location != null ? Icons.location_on : Icons.location_off,
                      color: _location != null ? Colors.green : Colors.orange,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        _location != null
                            ? 'Localisation capturée (±${_location!['accuracy'].toStringAsFixed(0)}m)'
                            : 'Localisation non disponible',
                        style: TextStyle(
                          color: _location != null ? Colors.green : Colors.orange,
                        ),
                      ),
                    ),
                    if (_location == null)
                      TextButton(
                        onPressed: _getLocation,
                        child: const Text('Réessayer'),
                      ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Clock In Button
            ElevatedButton.icon(
              onPressed: _isLoading ? null : _handleClockIn,
              icon: const Icon(Icons.play_arrow),
              label: _isLoading
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                      ),
                    )
                  : const Text('Commencer le travail'),
              style: ElevatedButton.styleFrom(
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
