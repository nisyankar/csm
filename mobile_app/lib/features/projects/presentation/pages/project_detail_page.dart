import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../providers/projects_providers.dart';
import '../../domain/entities/project.dart';

class ProjectDetailPage extends ConsumerStatefulWidget {
  final int projectId;

  const ProjectDetailPage({
    super.key,
    required this.projectId,
  });

  @override
  ConsumerState<ProjectDetailPage> createState() => _ProjectDetailPageState();
}

class _ProjectDetailPageState extends ConsumerState<ProjectDetailPage> {
  @override
  void initState() {
    super.initState();
    // Load project details when page opens
    Future.microtask(() {
      ref.read(projectDetailProvider.notifier).loadProject(widget.projectId);
    });
  }

  Future<void> _onRefresh() async {
    await ref.read(projectDetailProvider.notifier).refresh(widget.projectId);
  }

  @override
  Widget build(BuildContext context) {
    final detailState = ref.watch(projectDetailProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Proje Detayı'),
        backgroundColor: Colors.blue,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _onRefresh,
          ),
        ],
      ),
      body: _buildBody(detailState, context),
    );
  }

  Widget _buildBody(detailState, BuildContext context) {
    if (detailState.isLoading && detailState.project == null) {
      return const Center(child: CircularProgressIndicator());
    }

    if (detailState.error != null && detailState.project == null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: Colors.red),
            const SizedBox(height: 16),
            Text(
              detailState.error!,
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

    if (detailState.project == null) {
      return const Center(
        child: Text('Proje bulunamadı'),
      );
    }

    return RefreshIndicator(
      onRefresh: _onRefresh,
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _ProjectHeader(project: detailState.project!),
            const SizedBox(height: 16),
            _ProjectInfo(project: detailState.project!),
            const SizedBox(height: 16),
            _ProjectFinancials(project: detailState.project!),
            const SizedBox(height: 16),
            _ProjectTimeline(project: detailState.project!),
            const SizedBox(height: 16),
            _ProjectManagers(project: detailState.project!),
          ],
        ),
      ),
    );
  }
}

// Project Header Widget
class _ProjectHeader extends StatelessWidget {
  final Project project;

  const _ProjectHeader({required this.project});

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [Colors.blue[700]!, Colors.blue[500]!],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    project.name,
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
                _StatusBadge(status: project.status),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              project.projectCode,
              style: const TextStyle(
                fontSize: 16,
                color: Colors.white70,
              ),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                const Icon(Icons.location_on, size: 16, color: Colors.white70),
                const SizedBox(width: 4),
                Expanded(
                  child: Text(
                    '${project.city}, ${project.location}',
                    style: const TextStyle(fontSize: 14, color: Colors.white70),
                  ),
                ),
              ],
            ),
            if (project.description != null) ...[
              const SizedBox(height: 12),
              Text(
                project.description!,
                style: const TextStyle(fontSize: 14, color: Colors.white70),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

// Project Info Widget
class _ProjectInfo extends StatelessWidget {
  final Project project;

  const _ProjectInfo({required this.project});

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Proje Bilgileri',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const Divider(),
            const SizedBox(height: 8),
            _InfoRow(label: 'Tip', value: project.typeDisplay),
            _InfoRow(label: 'Öncelik', value: project.priorityDisplay),
            if (project.clientName != null)
              _InfoRow(label: 'Müşteri', value: project.clientName!),
            if (project.contactPhone != null)
              _InfoRow(label: 'Telefon', value: project.contactPhone!),
            if (project.contactEmail != null)
              _InfoRow(label: 'E-posta', value: project.contactEmail!),
            if (project.estimatedEmployees != null)
              _InfoRow(
                label: 'Tahmini Çalışan',
                value: '${project.estimatedEmployees} kişi',
              ),
          ],
        ),
      ),
    );
  }
}

// Project Financials Widget
class _ProjectFinancials extends StatelessWidget {
  final Project project;

  const _ProjectFinancials({required this.project});

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Finansal Bilgiler',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const Divider(),
            const SizedBox(height: 8),
            if (project.budget != null) ...[
              _InfoRow(
                label: 'Toplam Bütçe',
                value: _formatCurrency(project.budget!),
              ),
              _InfoRow(
                label: 'Harcanan',
                value: _formatCurrency(project.spentAmount),
              ),
              _InfoRow(
                label: 'Kalan',
                value: _formatCurrency(project.remainingBudget),
                valueColor:
                    project.remainingBudget < 0 ? Colors.red : Colors.green,
              ),
              const SizedBox(height: 12),
              // Budget Progress Bar
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Bütçe Kullanımı',
                        style: TextStyle(fontWeight: FontWeight.w600),
                      ),
                      Text(
                        '${project.budgetUsagePercentage.toStringAsFixed(1)}%',
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: project.budgetUsagePercentage > 100
                              ? Colors.red
                              : Colors.blue,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  LinearProgressIndicator(
                    value: (project.budgetUsagePercentage / 100).clamp(0.0, 1.0),
                    backgroundColor: Colors.grey[300],
                    valueColor: AlwaysStoppedAnimation(
                      project.budgetUsagePercentage > 100
                          ? Colors.red
                          : project.budgetUsagePercentage > 80
                              ? Colors.orange
                              : Colors.green,
                    ),
                    minHeight: 10,
                  ),
                ],
              ),
            ] else
              const Text(
                'Bütçe bilgisi girilmemiş',
                style: TextStyle(color: Colors.grey),
              ),
          ],
        ),
      ),
    );
  }

  String _formatCurrency(double amount) {
    final formatter = NumberFormat.currency(
      locale: 'tr_TR',
      symbol: '₺',
      decimalDigits: 2,
    );
    return formatter.format(amount);
  }
}

// Project Timeline Widget
class _ProjectTimeline extends StatelessWidget {
  final Project project;

  const _ProjectTimeline({required this.project});

  @override
  Widget build(BuildContext context) {
    final dateFormat = DateFormat('dd/MM/yyyy', 'tr_TR');

    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Zaman Çizelgesi',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const Divider(),
            const SizedBox(height: 8),
            _InfoRow(
              label: 'Başlangıç',
              value: dateFormat.format(project.startDate),
            ),
            _InfoRow(
              label: 'Planlanan Bitiş',
              value: dateFormat.format(project.plannedEndDate),
            ),
            if (project.actualEndDate != null)
              _InfoRow(
                label: 'Gerçek Bitiş',
                value: dateFormat.format(project.actualEndDate!),
              ),
            const SizedBox(height: 8),
            _InfoRow(
              label: 'Kalan Gün',
              value: project.daysRemaining > 0
                  ? '${project.daysRemaining} gün'
                  : 'Süre doldu',
              valueColor: project.daysRemaining < 0 ? Colors.red : null,
            ),
            if (project.isDelayed)
              Container(
                margin: const EdgeInsets.only(top: 12),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.red[50],
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.red[300]!),
                ),
                child: Row(
                  children: [
                    Icon(Icons.warning, color: Colors.red[700], size: 20),
                    const SizedBox(width: 8),
                    Text(
                      'Proje gecikmiş',
                      style: TextStyle(
                        color: Colors.red[700],
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }
}

// Project Managers Widget
class _ProjectManagers extends StatelessWidget {
  final Project project;

  const _ProjectManagers({required this.project});

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Yönetim',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const Divider(),
            const SizedBox(height: 8),
            if (project.projectManagerName != null)
              _ManagerCard(
                title: 'Proje Müdürü',
                name: project.projectManagerName!,
                icon: Icons.person,
              ),
            if (project.siteManagerName != null) ...[
              const SizedBox(height: 8),
              _ManagerCard(
                title: 'Şantiye Şefi',
                name: project.siteManagerName!,
                icon: Icons.engineering,
              ),
            ],
            if (project.projectManagerName == null &&
                project.siteManagerName == null)
              const Text(
                'Yönetici atanmamış',
                style: TextStyle(color: Colors.grey),
              ),
          ],
        ),
      ),
    );
  }
}

// Helper Widgets
class _StatusBadge extends StatelessWidget {
  final String status;

  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    final config = _getStatusConfig(status);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        config['label'],
        style: TextStyle(
          color: config['color'],
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

class _InfoRow extends StatelessWidget {
  final String label;
  final String value;
  final Color? valueColor;

  const _InfoRow({
    required this.label,
    required this.value,
    this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              label,
              style: TextStyle(
                fontWeight: FontWeight.w600,
                color: Colors.grey[700],
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                color: valueColor ?? Colors.black87,
                fontWeight: valueColor != null ? FontWeight.bold : null,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ManagerCard extends StatelessWidget {
  final String title;
  final String name;
  final IconData icon;

  const _ManagerCard({
    required this.title,
    required this.name,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.blue[50],
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: Colors.blue[200]!),
      ),
      child: Row(
        children: [
          CircleAvatar(
            backgroundColor: Colors.blue[700],
            child: Icon(icon, color: Colors.white, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[600],
                  ),
                ),
                Text(
                  name,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}