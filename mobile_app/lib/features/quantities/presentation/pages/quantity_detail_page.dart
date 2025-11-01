import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../providers/quantities_providers.dart';
import '../../domain/entities/quantity.dart';

class QuantityDetailPage extends ConsumerStatefulWidget {
  final int quantityId;

  const QuantityDetailPage({
    super.key,
    required this.quantityId,
  });

  @override
  ConsumerState<QuantityDetailPage> createState() => _QuantityDetailPageState();
}

class _QuantityDetailPageState extends ConsumerState<QuantityDetailPage> {
  @override
  void initState() {
    super.initState();
    // Load quantity details when page opens
    Future.microtask(() {
      ref.read(quantityDetailProvider.notifier).loadQuantity(widget.quantityId);
    });
  }

  Future<void> _onRefresh() async {
    await ref.read(quantityDetailProvider.notifier).refresh(widget.quantityId);
  }

  Future<void> _onVerify() async {
    final confirmed = await _showConfirmDialog(
      'Metrajı Doğrula',
      'Bu metrajı doğrulamak istediğinize emin misiniz?',
    );

    if (confirmed == true) {
      await ref.read(quantityDetailProvider.notifier).verify(widget.quantityId);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Metraj doğrulandı'),
            backgroundColor: Colors.green,
          ),
        );
        // Otomatik refresh
        await _onRefresh();
      }
    }
  }

  Future<void> _onApprove() async {
    final confirmed = await _showConfirmDialog(
      'Metrajı Onayla',
      'Bu metrajı onaylamak istediğinize emin misiniz?',
    );

    if (confirmed == true) {
      await ref.read(quantityDetailProvider.notifier).approve(widget.quantityId);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Metraj onaylandı'),
            backgroundColor: Colors.green,
          ),
        );
        // Otomatik refresh
        await _onRefresh();
      }
    }
  }

  Future<bool?> _showConfirmDialog(String title, String message) {
    return showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('İptal'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(true),
            child: const Text('Onayla'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final detailState = ref.watch(quantityDetailProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Metraj Detayı'),
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
      bottomNavigationBar: _buildActionButtons(detailState),
    );
  }

  Widget _buildBody(detailState, BuildContext context) {
    if (detailState.isLoading && detailState.quantity == null) {
      return const Center(child: CircularProgressIndicator());
    }

    if (detailState.error != null && detailState.quantity == null) {
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

    if (detailState.quantity == null) {
      return const Center(
        child: Text('Metraj bulunamadı'),
      );
    }

    return RefreshIndicator(
      onRefresh: _onRefresh,
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _QuantityHeader(quantity: detailState.quantity!),
            const SizedBox(height: 16),
            _WorkItemInfo(quantity: detailState.quantity!),
            const SizedBox(height: 16),
            _QuantityMetrics(quantity: detailState.quantity!),
            const SizedBox(height: 16),
            _LocationInfo(quantity: detailState.quantity!),
            const SizedBox(height: 16),
            _MeasurementInfo(quantity: detailState.quantity!),
            const SizedBox(height: 16),
            _ApprovalInfo(quantity: detailState.quantity!),
            if (detailState.quantity!.notes != null) ...[
              const SizedBox(height: 16),
              _NotesSection(notes: detailState.quantity!.notes!),
            ],
          ],
        ),
      ),
    );
  }

  Widget? _buildActionButtons(detailState) {
    if (detailState.quantity == null) return null;

    final quantity = detailState.quantity!;
    final showButtons = !quantity.isVerified || (quantity.isVerified && !quantity.isApproved);

    if (!showButtons) return null;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.3),
            blurRadius: 4,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: Row(
        children: [
          if (!quantity.isVerified)
            Expanded(
              child: ElevatedButton.icon(
                onPressed: detailState.isLoading ? null : _onVerify,
                icon: const Icon(Icons.verified),
                label: const Text('Doğrula'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.blue,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            ),
          if (quantity.isVerified && !quantity.isApproved) ...[
            if (!quantity.isVerified) const SizedBox(width: 8),
            Expanded(
              child: ElevatedButton.icon(
                onPressed: detailState.isLoading ? null : _onApprove,
                icon: const Icon(Icons.check_circle),
                label: const Text('Onayla'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// Quantity Header Widget
class _QuantityHeader extends StatelessWidget {
  final Quantity quantity;

  const _QuantityHeader({required this.quantity});

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
                    quantity.workItemName,
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
                _StatusBadge(status: quantity.statusLabel),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              quantity.workItemCode,
              style: const TextStyle(
                fontSize: 16,
                color: Colors.white70,
              ),
            ),
            if (quantity.projectName != null) ...[
              const SizedBox(height: 12),
              Row(
                children: [
                  const Icon(Icons.business, size: 16, color: Colors.white70),
                  const SizedBox(width: 4),
                  Expanded(
                    child: Text(
                      quantity.projectName!,
                      style: const TextStyle(fontSize: 14, color: Colors.white70),
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }
}

// Work Item Info Widget
class _WorkItemInfo extends StatelessWidget {
  final Quantity quantity;

  const _WorkItemInfo({required this.quantity});

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
              'İş Kalemi Bilgileri',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const Divider(),
            const SizedBox(height: 8),
            _InfoRow(label: 'İş Kalemi', value: quantity.workItemName),
            _InfoRow(label: 'Kodu', value: quantity.workItemCode),
            _InfoRow(label: 'Birim', value: quantity.workItemUnit),
          ],
        ),
      ),
    );
  }
}

// Quantity Metrics Widget
class _QuantityMetrics extends StatelessWidget {
  final Quantity quantity;

  const _QuantityMetrics({required this.quantity});

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
              'Metraj Bilgileri',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const Divider(),
            const SizedBox(height: 8),
            _InfoRow(
              label: 'Planlanan Miktar',
              value: '${quantity.plannedQuantity.toStringAsFixed(2)} ${quantity.unit}',
            ),
            _InfoRow(
              label: 'Tamamlanan Miktar',
              value: '${quantity.completedQuantity.toStringAsFixed(2)} ${quantity.unit}',
            ),
            _InfoRow(
              label: 'Kalan Miktar',
              value: '${quantity.remainingQuantity.toStringAsFixed(2)} ${quantity.unit}',
              valueColor: quantity.remainingQuantity < 0 ? Colors.red : Colors.green,
            ),
            const SizedBox(height: 12),
            // Progress Bar
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Tamamlanma Oranı',
                      style: TextStyle(fontWeight: FontWeight.w600),
                    ),
                    Text(
                      '${quantity.completionPercentage.toStringAsFixed(1)}%',
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        color: quantity.completionPercentage >= 100
                            ? Colors.green
                            : Colors.blue,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
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
                  minHeight: 10,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

// Location Info Widget
class _LocationInfo extends StatelessWidget {
  final Quantity quantity;

  const _LocationInfo({required this.quantity});

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
              'Konum Bilgileri',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const Divider(),
            const SizedBox(height: 8),
            if (quantity.projectStructureName != null)
              _InfoRow(label: 'Yapı', value: quantity.projectStructureName!),
            if (quantity.projectFloorName != null)
              _InfoRow(label: 'Kat', value: quantity.projectFloorName!),
            if (quantity.projectUnitCode != null)
              _InfoRow(label: 'Birim', value: quantity.projectUnitCode!),
            if (quantity.projectStructureName == null &&
                quantity.projectFloorName == null &&
                quantity.projectUnitCode == null)
              const Text(
                'Konum bilgisi belirtilmemiş',
                style: TextStyle(color: Colors.grey),
              ),
          ],
        ),
      ),
    );
  }
}

// Measurement Info Widget
class _MeasurementInfo extends StatelessWidget {
  final Quantity quantity;

  const _MeasurementInfo({required this.quantity});

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
              'Ölçüm Bilgileri',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const Divider(),
            const SizedBox(height: 8),
            _InfoRow(
              label: 'Ölçüm Tarihi',
              value: dateFormat.format(quantity.measurementDate),
            ),
            if (quantity.measurementMethod != null)
              _InfoRow(
                label: 'Ölçüm Yöntemi',
                value: _getMeasurementMethodLabel(quantity.measurementMethod!),
              ),
          ],
        ),
      ),
    );
  }

  String _getMeasurementMethodLabel(String method) {
    switch (method) {
      case 'manual':
        return 'Manuel';
      case 'calculated':
        return 'Hesaplanmış';
      case 'imported':
        return 'İçe Aktarılmış';
      default:
        return method;
    }
  }
}

// Approval Info Widget
class _ApprovalInfo extends StatelessWidget {
  final Quantity quantity;

  const _ApprovalInfo({required this.quantity});

  @override
  Widget build(BuildContext context) {
    final dateFormat = DateFormat('dd/MM/yyyy HH:mm', 'tr_TR');

    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Onay Bilgileri',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const Divider(),
            const SizedBox(height: 8),
            // Verification Status
            if (quantity.isVerified) ...[
              _ApprovalStatusCard(
                icon: Icons.verified,
                title: 'Doğrulama',
                status: 'Doğrulandı',
                statusColor: Colors.green,
                by: quantity.verifiedByName,
                at: quantity.verifiedAt != null
                    ? dateFormat.format(quantity.verifiedAt!)
                    : null,
              ),
            ] else ...[
              _ApprovalStatusCard(
                icon: Icons.pending,
                title: 'Doğrulama',
                status: 'Beklemede',
                statusColor: Colors.orange,
              ),
            ],
            const SizedBox(height: 12),
            // Approval Status
            if (quantity.isApproved) ...[
              _ApprovalStatusCard(
                icon: Icons.check_circle,
                title: 'Onay',
                status: 'Onaylandı',
                statusColor: Colors.blue,
                by: quantity.approvedByName,
                at: quantity.approvedAt != null
                    ? dateFormat.format(quantity.approvedAt!)
                    : null,
              ),
            ] else if (quantity.isVerified) ...[
              _ApprovalStatusCard(
                icon: Icons.pending,
                title: 'Onay',
                status: 'Onay Bekliyor',
                statusColor: Colors.orange,
              ),
            ] else ...[
              _ApprovalStatusCard(
                icon: Icons.block,
                title: 'Onay',
                status: 'Önce doğrulanmalı',
                statusColor: Colors.grey,
              ),
            ],
          ],
        ),
      ),
    );
  }
}

// Notes Section Widget
class _NotesSection extends StatelessWidget {
  final String notes;

  const _NotesSection({required this.notes});

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
              'Notlar',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const Divider(),
            const SizedBox(height: 8),
            Text(
              notes,
              style: const TextStyle(fontSize: 14),
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
            width: 140,
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

class _ApprovalStatusCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String status;
  final Color statusColor;
  final String? by;
  final String? at;

  const _ApprovalStatusCard({
    required this.icon,
    required this.title,
    required this.status,
    required this.statusColor,
    this.by,
    this.at,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: statusColor.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: statusColor.withOpacity(0.3)),
      ),
      child: Row(
        children: [
          CircleAvatar(
            backgroundColor: statusColor,
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
                  status,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: statusColor,
                  ),
                ),
                if (by != null) ...[
                  const SizedBox(height: 4),
                  Text(
                    'Onaylayan: $by',
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey[700],
                    ),
                  ),
                ],
                if (at != null) ...[
                  Text(
                    'Tarih: $at',
                    style: TextStyle(
                      fontSize: 11,
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
