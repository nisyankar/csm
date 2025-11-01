import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/projects_providers.dart';
import '../../domain/entities/project.dart';
import 'project_detail_page.dart';

class ProjectsListPage extends ConsumerStatefulWidget {
  const ProjectsListPage({super.key});

  @override
  ConsumerState<ProjectsListPage> createState() => _ProjectsListPageState();
}

class _ProjectsListPageState extends ConsumerState<ProjectsListPage> {
  final ScrollController _scrollController = ScrollController();
  String? _selectedStatus;
  String? _selectedPriority;
  String? _searchQuery;

  @override
  void initState() {
    super.initState();
    // Load projects when page opens (with refresh to clear old data)
    Future.microtask(() {
      ref.read(projectsProvider.notifier).loadProjects(refresh: true);
    });

    // Setup scroll listener for pagination
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent * 0.9) {
      // Load more when scrolled to 90%
      ref.read(projectsProvider.notifier).loadMore(
            search: _searchQuery,
            status: _selectedStatus,
            priority: _selectedPriority,
          );
    }
  }

  Future<void> _onRefresh() async {
    await ref.read(projectsProvider.notifier).refresh(
          search: _searchQuery,
          status: _selectedStatus,
          priority: _selectedPriority,
        );
  }

  void _onSearch(String query) {
    setState(() {
      _searchQuery = query.isEmpty ? null : query;
    });
    ref.read(projectsProvider.notifier).loadProjects(
          search: _searchQuery,
          status: _selectedStatus,
          priority: _selectedPriority,
          refresh: true,
        );
  }

  void _onFilterStatus(String? status) {
    setState(() {
      _selectedStatus = status;
    });
    ref.read(projectsProvider.notifier).loadProjects(
          search: _searchQuery,
          status: _selectedStatus,
          priority: _selectedPriority,
          refresh: true,
        );
  }

  void _onFilterPriority(String? priority) {
    setState(() {
      _selectedPriority = priority;
    });
    ref.read(projectsProvider.notifier).loadProjects(
          search: _searchQuery,
          status: _selectedStatus,
          priority: _selectedPriority,
          refresh: true,
        );
  }

  @override
  Widget build(BuildContext context) {
    final projectsState = ref.watch(projectsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Projeler'),
        backgroundColor: Colors.blue,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.filter_list),
            onPressed: _showFilterDialog,
          ),
        ],
      ),
      body: Column(
        children: [
          // Search Bar
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: TextField(
              decoration: InputDecoration(
                hintText: 'Proje ara...',
                prefixIcon: const Icon(Icons.search),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                filled: true,
                fillColor: Colors.grey[100],
              ),
              onChanged: _onSearch,
            ),
          ),

          // Filter Chips
          if (_selectedStatus != null || _selectedPriority != null)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0),
              child: Wrap(
                spacing: 8,
                children: [
                  if (_selectedStatus != null)
                    Chip(
                      label: Text(_getStatusLabel(_selectedStatus!)),
                      deleteIcon: const Icon(Icons.close, size: 18),
                      onDeleted: () => _onFilterStatus(null),
                    ),
                  if (_selectedPriority != null)
                    Chip(
                      label: Text(_getPriorityLabel(_selectedPriority!)),
                      deleteIcon: const Icon(Icons.close, size: 18),
                      onDeleted: () => _onFilterPriority(null),
                    ),
                ],
              ),
            ),

          // Projects List
          Expanded(
            child: _buildProjectsList(projectsState),
          ),
        ],
      ),
    );
  }

  Widget _buildProjectsList(projectsState) {
    if (projectsState.isLoading && projectsState.projects.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }

    if (projectsState.error != null && projectsState.projects.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: Colors.red),
            const SizedBox(height: 16),
            Text(
              projectsState.error!,
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.red),
            ),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: _onRefresh,
              child: const Text('Tekrar Dene'),
            ),
          ],
        ),
      );
    }

    if (projectsState.projects.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.folder_open, size: 64, color: Colors.grey[400]),
            const SizedBox(height: 16),
            Text(
              'Proje bulunamadı',
              style: TextStyle(fontSize: 18, color: Colors.grey[600]),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _onRefresh,
      child: ListView.builder(
        controller: _scrollController,
        padding: const EdgeInsets.all(16),
        itemCount: projectsState.projects.length +
            (projectsState.isLoading ? 1 : 0),
        itemBuilder: (context, index) {
          if (index == projectsState.projects.length) {
            return const Center(
              child: Padding(
                padding: EdgeInsets.all(16.0),
                child: CircularProgressIndicator(),
              ),
            );
          }

          final project = projectsState.projects[index];
          return _ProjectCard(
            project: project,
            onTap: () => _navigateToDetail(project.id),
          );
        },
      ),
    );
  }

  void _navigateToDetail(int projectId) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => ProjectDetailPage(projectId: projectId),
      ),
    );
  }

  void _showFilterDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Filtrele'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Durum:', style: TextStyle(fontWeight: FontWeight.bold)),
            DropdownButton<String>(
              value: _selectedStatus,
              isExpanded: true,
              hint: const Text('Durum seçin'),
              items: [
                const DropdownMenuItem(value: null, child: Text('Tümü')),
                ..._statusOptions.entries.map(
                  (e) => DropdownMenuItem(value: e.key, child: Text(e.value)),
                ),
              ],
              onChanged: (value) {
                _onFilterStatus(value);
                Navigator.pop(context);
              },
            ),
            const SizedBox(height: 16),
            const Text('Öncelik:', style: TextStyle(fontWeight: FontWeight.bold)),
            DropdownButton<String>(
              value: _selectedPriority,
              isExpanded: true,
              hint: const Text('Öncelik seçin'),
              items: [
                const DropdownMenuItem(value: null, child: Text('Tümü')),
                ..._priorityOptions.entries.map(
                  (e) => DropdownMenuItem(value: e.key, child: Text(e.value)),
                ),
              ],
              onChanged: (value) {
                _onFilterPriority(value);
                Navigator.pop(context);
              },
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              _onFilterStatus(null);
              _onFilterPriority(null);
              Navigator.pop(context);
            },
            child: const Text('Temizle'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Kapat'),
          ),
        ],
      ),
    );
  }

  static const Map<String, String> _statusOptions = {
    'planning': 'Planlamada',
    'active': 'Aktif',
    'on_hold': 'Beklemede',
    'completed': 'Tamamlandı',
    'cancelled': 'İptal',
  };

  static const Map<String, String> _priorityOptions = {
    'low': 'Düşük',
    'medium': 'Orta',
    'high': 'Yüksek',
    'critical': 'Kritik',
  };

  String _getStatusLabel(String status) => _statusOptions[status] ?? status;
  String _getPriorityLabel(String priority) =>
      _priorityOptions[priority] ?? priority;
}

// Project Card Widget
class _ProjectCard extends StatelessWidget {
  final Project project;
  final VoidCallback onTap;

  const _ProjectCard({
    required this.project,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header Row
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          project.name,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          project.projectCode,
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ),
                  _StatusBadge(status: project.status),
                ],
              ),

              const SizedBox(height: 12),

              // Location
              Row(
                children: [
                  Icon(Icons.location_on, size: 16, color: Colors.grey[600]),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      '${project.city}, ${project.location}',
                      style: TextStyle(fontSize: 14, color: Colors.grey[700]),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 8),

              // Budget & Progress
              Row(
                children: [
                  Expanded(
                    child: _InfoChip(
                      icon: Icons.account_balance_wallet,
                      label: 'Bütçe',
                      value: project.budget != null
                          ? '${(project.budget! / 1000000).toStringAsFixed(1)}M ₺'
                          : 'Yok',
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _InfoChip(
                      icon: Icons.calendar_today,
                      label: 'Kalan',
                      value: project.daysRemaining > 0
                          ? '${project.daysRemaining} gün'
                          : 'Geçti',
                      valueColor:
                          project.daysRemaining < 0 ? Colors.red : null,
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 8),

              // Priority Badge
              Align(
                alignment: Alignment.centerRight,
                child: _PriorityBadge(priority: project.priority),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// Status Badge Widget
class _StatusBadge extends StatelessWidget {
  final String status;

  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    final config = _getStatusConfig(status);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: config['color'],
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        config['label'],
        style: const TextStyle(
          color: Colors.white,
          fontSize: 12,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }

  Map<String, dynamic> _getStatusConfig(String status) {
    switch (status) {
      case 'planning':
        return {'label': 'Planlamada', 'color': Colors.orange};
      case 'active':
        return {'label': 'Aktif', 'color': Colors.green};
      case 'on_hold':
        return {'label': 'Beklemede', 'color': Colors.amber};
      case 'completed':
        return {'label': 'Tamamlandı', 'color': Colors.blue};
      case 'cancelled':
        return {'label': 'İptal', 'color': Colors.red};
      default:
        return {'label': status, 'color': Colors.grey};
    }
  }
}

// Priority Badge Widget
class _PriorityBadge extends StatelessWidget {
  final String priority;

  const _PriorityBadge({required this.priority});

  @override
  Widget build(BuildContext context) {
    final config = _getPriorityConfig(priority);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        border: Border.all(color: config['color'], width: 2),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.flag, size: 14, color: config['color']),
          const SizedBox(width: 4),
          Text(
            config['label'],
            style: TextStyle(
              color: config['color'],
              fontSize: 12,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  Map<String, dynamic> _getPriorityConfig(String priority) {
    switch (priority) {
      case 'low':
        return {'label': 'Düşük', 'color': Colors.green};
      case 'medium':
        return {'label': 'Orta', 'color': Colors.orange};
      case 'high':
        return {'label': 'Yüksek', 'color': Colors.red[700]};
      case 'critical':
        return {'label': 'Kritik', 'color': Colors.red[900]};
      default:
        return {'label': priority, 'color': Colors.grey};
    }
  }
}

// Info Chip Widget
class _InfoChip extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final Color? valueColor;

  const _InfoChip({
    required this.icon,
    required this.label,
    required this.value,
    this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: Colors.grey[100],
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: Colors.grey[600]),
          const SizedBox(width: 4),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: TextStyle(fontSize: 10, color: Colors.grey[600]),
              ),
              Text(
                value,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: valueColor ?? Colors.black87,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
