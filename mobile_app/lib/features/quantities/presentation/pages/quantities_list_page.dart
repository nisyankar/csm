import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../providers/quantities_providers.dart';
import '../../domain/entities/quantity.dart';
import 'quantity_detail_page.dart';

class QuantitiesListPage extends ConsumerStatefulWidget {
  const QuantitiesListPage({super.key});

  @override
  ConsumerState<QuantitiesListPage> createState() => _QuantitiesListPageState();
}

class _QuantitiesListPageState extends ConsumerState<QuantitiesListPage> {
  final ScrollController _scrollController = ScrollController();
  String? _searchQuery;
  int? _selectedProjectId;
  bool? _verifiedFilter;
  bool? _approvedFilter;

  @override
  void initState() {
    super.initState();
    // Load quantities when page opens
    Future.microtask(() {
      ref.read(quantitiesProvider.notifier).loadQuantities(refresh: true);
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
      ref.read(quantitiesProvider.notifier).loadMore(
            search: _searchQuery,
            projectId: _selectedProjectId,
            verified: _verifiedFilter,
            approved: _approvedFilter,
          );
    }
  }

  Future<void> _onRefresh() async {
    await ref.read(quantitiesProvider.notifier).refresh(
          search: _searchQuery,
          projectId: _selectedProjectId,
          verified: _verifiedFilter,
          approved: _approvedFilter,
        );
  }

  void _onSearch(String query) {
    setState(() {
      _searchQuery = query.isEmpty ? null : query;
    });
    ref.read(quantitiesProvider.notifier).loadQuantities(
          search: _searchQuery,
          projectId: _selectedProjectId,
          verified: _verifiedFilter,
          approved: _approvedFilter,
          refresh: true,
        );
  }

  void _onFilterVerified(bool? verified) {
    setState(() {
      _verifiedFilter = verified;
    });
    ref.read(quantitiesProvider.notifier).loadQuantities(
          search: _searchQuery,
          projectId: _selectedProjectId,
          verified: _verifiedFilter,
          approved: _approvedFilter,
          refresh: true,
        );
  }

  void _onFilterApproved(bool? approved) {
    setState(() {
      _approvedFilter = approved;
    });
    ref.read(quantitiesProvider.notifier).loadQuantities(
          search: _searchQuery,
          projectId: _selectedProjectId,
          verified: _verifiedFilter,
          approved: _approvedFilter,
          refresh: true,
        );
  }

  @override
  Widget build(BuildContext context) {
    final quantitiesState = ref.watch(quantitiesProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Metrajlar'),
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
                hintText: 'Metraj ara...',
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
          if (_verifiedFilter != null || _approvedFilter != null)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16.0),
              child: Wrap(
                spacing: 8,
                children: [
                  if (_verifiedFilter != null)
                    Chip(
                      label: Text(_verifiedFilter! ? 'Doğrulanmış' : 'Doğrulanmamış'),
                      deleteIcon: const Icon(Icons.close, size: 18),
                      onDeleted: () => _onFilterVerified(null),
                    ),
                  if (_approvedFilter != null)
                    Chip(
                      label: Text(_approvedFilter! ? 'Onaylı' : 'Onaysız'),
                      deleteIcon: const Icon(Icons.close, size: 18),
                      onDeleted: () => _onFilterApproved(null),
                    ),
                ],
              ),
            ),

          // Quantities List
          Expanded(
            child: _buildQuantitiesList(quantitiesState),
          ),
        ],
      ),
    );
  }

  Widget _buildQuantitiesList(quantitiesState) {
    if (quantitiesState.isLoading && quantitiesState.quantities.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }

    if (quantitiesState.error != null && quantitiesState.quantities.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: Colors.red),
            const SizedBox(height: 16),
            Text(
              quantitiesState.error!,
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

    if (quantitiesState.quantities.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.straighten, size: 64, color: Colors.grey[400]),
            const SizedBox(height: 16),
            Text(
              'Metraj bulunamadı',
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
        itemCount: quantitiesState.quantities.length +
            (quantitiesState.isLoading ? 1 : 0),
        itemBuilder: (context, index) {
          if (index == quantitiesState.quantities.length) {
            return const Center(
              child: Padding(
                padding: EdgeInsets.all(16.0),
                child: CircularProgressIndicator(),
              ),
            );
          }

          final quantity = quantitiesState.quantities[index];
          return _QuantityCard(
            quantity: quantity,
            onTap: () => _navigateToDetail(quantity.id),
          );
        },
      ),
    );
  }

  void _navigateToDetail(int quantityId) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => QuantityDetailPage(quantityId: quantityId),
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
            const Text('Doğrulama:', style: TextStyle(fontWeight: FontWeight.bold)),
            DropdownButton<bool?>(
              value: _verifiedFilter,
              isExpanded: true,
              hint: const Text('Doğrulama durumu seçin'),
              items: const [
                DropdownMenuItem(value: null, child: Text('Tümü')),
                DropdownMenuItem(value: true, child: Text('Doğrulanmış')),
                DropdownMenuItem(value: false, child: Text('Doğrulanmamış')),
              ],
              onChanged: (value) {
                _onFilterVerified(value);
                Navigator.pop(context);
              },
            ),
            const SizedBox(height: 16),
            const Text('Onay:', style: TextStyle(fontWeight: FontWeight.bold)),
            DropdownButton<bool?>(
              value: _approvedFilter,
              isExpanded: true,
              hint: const Text('Onay durumu seçin'),
              items: const [
                DropdownMenuItem(value: null, child: Text('Tümü')),
                DropdownMenuItem(value: true, child: Text('Onaylı')),
                DropdownMenuItem(value: false, child: Text('Onaysız')),
              ],
              onChanged: (value) {
                _onFilterApproved(value);
                Navigator.pop(context);
              },
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () {
              _onFilterVerified(null);
              _onFilterApproved(null);
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
}

// Quantity Card Widget
class _QuantityCard extends StatelessWidget {
  final Quantity quantity;
  final VoidCallback onTap;

  const _QuantityCard({
    required this.quantity,
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
                          quantity.workItemName,
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          quantity.workItemCode,
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ),
                  _StatusBadge(status: quantity.statusLabel),
                ],
              ),

              const SizedBox(height: 12),

              // Project Name
              if (quantity.projectName != null) ...[
                Row(
                  children: [
                    Icon(Icons.business, size: 16, color: Colors.grey[600]),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        quantity.projectName!,
                        style: TextStyle(fontSize: 14, color: Colors.grey[700]),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
              ],

              // Location
              Row(
                children: [
                  Icon(Icons.location_on, size: 16, color: Colors.grey[600]),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      quantity.locationLabel,
                      style: TextStyle(fontSize: 14, color: Colors.grey[700]),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 12),

              // Quantity Info
              Row(
                children: [
                  Expanded(
                    child: _InfoChip(
                      icon: Icons.trending_up,
                      label: 'Planlanan',
                      value: '${quantity.plannedQuantity.toStringAsFixed(2)} ${quantity.unit}',
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _InfoChip(
                      icon: Icons.done_all,
                      label: 'Tamamlanan',
                      value: '${quantity.completedQuantity.toStringAsFixed(2)} ${quantity.unit}',
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 12),

              // Progress Bar
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Tamamlanma',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: Colors.grey[700],
                        ),
                      ),
                      Text(
                        '${quantity.completionPercentage.toStringAsFixed(1)}%',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: quantity.completionPercentage >= 100
                              ? Colors.green
                              : Colors.blue,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  LinearProgressIndicator(
                    value: (quantity.completionPercentage / 100).clamp(0.0, 1.0),
                    backgroundColor: Colors.grey[300],
                    valueColor: AlwaysStoppedAnimation(
                      quantity.completionPercentage >= 100
                          ? Colors.green
                          : quantity.completionPercentage >= 75
                              ? Colors.blue
                              : quantity.completionPercentage >= 50
                                  ? Colors.orange
                                  : Colors.red,
                    ),
                    minHeight: 8,
                  ),
                ],
              ),

              const SizedBox(height: 8),

              // Measurement Date
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Row(
                    children: [
                      Icon(Icons.calendar_today, size: 14, color: Colors.grey[600]),
                      const SizedBox(width: 4),
                      Text(
                        DateFormat('dd/MM/yyyy', 'tr_TR').format(quantity.measurementDate),
                        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      ),
                    ],
                  ),
                  if (quantity.isVerified)
                    Row(
                      children: [
                        Icon(Icons.verified, size: 14, color: Colors.green[700]),
                        const SizedBox(width: 4),
                        Text(
                          'Doğrulandı',
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.green[700],
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  if (quantity.isApproved)
                    Row(
                      children: [
                        Icon(Icons.check_circle, size: 14, color: Colors.blue[700]),
                        const SizedBox(width: 4),
                        Text(
                          'Onaylı',
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.blue[700],
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                ],
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
      case 'Onaylı':
        return {'label': 'Onaylı', 'color': Colors.green};
      case 'Doğrulandı':
        return {'label': 'Doğrulandı', 'color': Colors.blue};
      case 'Beklemede':
        return {'label': 'Beklemede', 'color': Colors.orange};
      default:
        return {'label': status, 'color': Colors.grey};
    }
  }
}

// Info Chip Widget
class _InfoChip extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _InfoChip({
    required this.icon,
    required this.label,
    required this.value,
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
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(fontSize: 10, color: Colors.grey[600]),
                ),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
