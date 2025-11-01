import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import '../../../../core/constants/app_constants.dart';
import '../../../../core/services/location_service.dart';
import '../../../../core/services/location_service_provider.dart';
import '../../../projects/presentation/providers/projects_providers.dart';
import '../providers/timesheet_providers.dart';

class TodayStatusPage extends ConsumerStatefulWidget {
  const TodayStatusPage({super.key});

  @override
  ConsumerState<TodayStatusPage> createState() => _TodayStatusPageState();
}

class _TodayStatusPageState extends ConsumerState<TodayStatusPage> {
  int? _selectedProjectId;
  String _selectedMethod = 'gps'; // GPS varsayılan yöntem
  final TextEditingController _notesController = TextEditingController();

  @override
  void initState() {
    super.initState();
    Future.microtask(() {
      ref.read(todayTimesheetProvider.notifier).loadTodayStatus();
      ref.read(projectsProvider.notifier).loadProjects();
    });
  }

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _onRefresh() async {
    await ref.read(todayTimesheetProvider.notifier).refresh();
  }

  Future<void> _onClockIn() async {
    final todayState = ref.read(todayTimesheetProvider);
    final projectId = todayState.currentProject?.id;

    if (projectId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Henüz bir projeye atanmadınız. Lütfen yöneticinizle iletişime geçin.'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    try {
      Map<String, double>? location;

      // GPS yöntemi seçildiyse konum al
      if (_selectedMethod == 'gps') {
        final locationService = ref.read(locationServiceProvider);

        try {
          final position = await locationService.getCurrentLocation();
          location = {
            'latitude': position.latitude,
            'longitude': position.longitude,
          };
        } on AppLocationServiceDisabledException catch (e) {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(e.toString()),
                backgroundColor: Colors.orange,
                action: SnackBarAction(
                  label: 'GPS Aç',
                  textColor: Colors.white,
                  onPressed: () => locationService.openLocationSettings(),
                ),
              ),
            );
          }
          return;
        } on AppLocationPermissionPermanentlyDeniedException catch (e) {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(e.toString()),
                backgroundColor: Colors.red,
                action: SnackBarAction(
                  label: 'Ayarlar',
                  textColor: Colors.white,
                  onPressed: () => locationService.openAppSettings(),
                ),
              ),
            );
          }
          return;
        } on AppLocationPermissionDeniedException catch (e) {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(e.toString()),
                backgroundColor: Colors.red,
              ),
            );
          }
          return;
        } on AppLocationException catch (e) {
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(e.toString()),
                backgroundColor: Colors.red,
              ),
            );
          }
          return;
        }
      }

      await ref.read(todayTimesheetProvider.notifier).clockIn(
            projectId: projectId,
            checkInMethod: _selectedMethod,
            location: location,
            notes: _notesController.text.isEmpty ? null : _notesController.text,
          );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Giriş başarıyla kaydedildi'),
            backgroundColor: Colors.green,
          ),
        );
        _notesController.clear();
        await _onRefresh();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Hata: ${e.toString()}'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  Future<void> _onClockOut() async {
    final confirmed = await _showConfirmDialog(
      'Çıkış Yap',
      'Çıkış yapmak istediğinize emin misiniz?',
    );

    if (confirmed == true) {
      try {
        Map<String, double>? location;

        // GPS yöntemi seçildiyse konum al
        if (_selectedMethod == 'gps') {
          final locationService = ref.read(locationServiceProvider);

          try {
            final position = await locationService.getCurrentLocation();
            location = {
              'latitude': position.latitude,
              'longitude': position.longitude,
            };
          } on AppLocationServiceDisabledException catch (e) {
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(e.toString()),
                  backgroundColor: Colors.orange,
                  action: SnackBarAction(
                    label: 'GPS Aç',
                    textColor: Colors.white,
                    onPressed: () => locationService.openLocationSettings(),
                  ),
                ),
              );
            }
            return;
          } on AppLocationPermissionPermanentlyDeniedException catch (e) {
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(e.toString()),
                  backgroundColor: Colors.red,
                  action: SnackBarAction(
                    label: 'Ayarlar',
                    textColor: Colors.white,
                    onPressed: () => locationService.openAppSettings(),
                  ),
                ),
              );
            }
            return;
          } on AppLocationPermissionDeniedException catch (e) {
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(e.toString()),
                  backgroundColor: Colors.red,
                ),
              );
            }
            return;
          } on AppLocationException catch (e) {
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(e.toString()),
                  backgroundColor: Colors.red,
                ),
              );
            }
            return;
          }
        }

        final state = ref.read(todayTimesheetProvider);
        await ref.read(todayTimesheetProvider.notifier).clockOut(
              timesheetId: state.activeTimesheet?.id,
              checkOutMethod: _selectedMethod,
              location: location,
              notes: _notesController.text.isEmpty ? null : _notesController.text,
            );

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Çıkış başarıyla kaydedildi'),
              backgroundColor: Colors.green,
            ),
          );
          _notesController.clear();
          await _onRefresh();
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Hata: ${e.toString()}'),
              backgroundColor: Colors.red,
            ),
          );
        }
      }
    }
  }

  Future<bool?> _showConfirmDialog(String title, String message) {
    return showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppBorderRadius.xl),
        ),
        title: Text(title),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('İptal'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: Colors.white,
            ),
            child: const Text('Onayla'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final todayState = ref.watch(todayTimesheetProvider);
    final projectsState = ref.watch(projectsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Bugünkü Puantaj'),
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _onRefresh,
          ),
        ],
      ),
      body: _buildBody(todayState, projectsState),
    );
  }

  Widget _buildBody(todayState, projectsState) {
    if (todayState.isLoading && todayState.activeTimesheet == null) {
      return const Center(child: CircularProgressIndicator());
    }

    if (todayState.error != null && todayState.activeTimesheet == null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: Colors.red),
            const SizedBox(height: 16),
            Text(
              todayState.error!,
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

    return RefreshIndicator(
      onRefresh: _onRefresh,
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(AppSpacing.md),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _buildStatusHeader(todayState),
            const SizedBox(height: AppSpacing.lg),
            if (todayState.isCheckedIn && todayState.activeTimesheet?.checkOutTime == null) ...[
              _buildCurrentTimesheetCard(todayState),
              const SizedBox(height: AppSpacing.lg),
              _buildNotesInput(),
              const SizedBox(height: AppSpacing.lg),
              _buildMethodSelector(),
              const SizedBox(height: AppSpacing.lg),
              _buildClockOutButton(todayState),
            ] else if (todayState.isCheckedIn && todayState.activeTimesheet?.checkOutTime != null) ...[
              _buildCurrentTimesheetCard(todayState),
              const SizedBox(height: AppSpacing.lg),
              _buildCompletedMessage(),
            ] else ...[
              _buildProjectInfo(todayState),
              const SizedBox(height: AppSpacing.lg),
              _buildMethodSelector(),
              const SizedBox(height: AppSpacing.lg),
              _buildNotesInput(),
              const SizedBox(height: AppSpacing.lg),
              _buildClockInButton(todayState),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildStatusHeader(todayState) {
    final now = DateTime.now();
    final dateFormat = DateFormat('EEEE, dd MMMM yyyy', 'tr_TR');

    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppBorderRadius.xl)),
      child: Container(
        padding: const EdgeInsets.all(AppSpacing.lg),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: todayState.isCheckedIn
                ? [Colors.green[600]!, Colors.green[400]!]
                : [AppColors.primary, Colors.blue[400]!],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(AppBorderRadius.xl),
        ),
        child: Column(
          children: [
            Icon(
              todayState.isCheckedIn ? Icons.check_circle : Icons.access_time,
              size: 64,
              color: Colors.white,
            ),
            const SizedBox(height: AppSpacing.sm),
            Text(
              todayState.isCheckedIn ? 'İşe Geldiniz' : 'İşe Gelmek İçin',
              style: AppTextStyles.h2.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: AppSpacing.xs),
            Text(
              dateFormat.format(now),
              style: AppTextStyles.bodyMedium.copyWith(
                color: Colors.white.withValues(alpha: 0.9),
              ),
            ),
            if (todayState.isCheckedIn && todayState.currentWorkingHours != null) ...[
              const SizedBox(height: AppSpacing.md),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: AppSpacing.md,
                  vertical: AppSpacing.sm,
                ),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(AppBorderRadius.md),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.timer, color: Colors.white, size: 20),
                    const SizedBox(width: AppSpacing.xs),
                    Text(
                      'Çalışma Süresi: ${todayState.currentWorkingHours}',
                      style: AppTextStyles.bodyLarge.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildCurrentTimesheetCard(todayState) {
    if (todayState.activeTimesheet == null) return const SizedBox.shrink();

    final timesheet = todayState.activeTimesheet!;

    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppBorderRadius.xl)),
      child: Padding(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Aktif Puantaj',
              style: AppTextStyles.h3.copyWith(fontWeight: FontWeight.bold),
            ),
            const Divider(),
            const SizedBox(height: AppSpacing.sm),
            if (timesheet.projectName != null)
              _InfoRow(
                icon: Icons.business,
                label: 'Proje',
                value: timesheet.projectName!,
              ),
            if (timesheet.checkInTime != null)
              _InfoRow(
                icon: Icons.login,
                label: 'Giriş Saati',
                value: timesheet.checkInTime!,
              ),
            if (timesheet.checkInMethod != null)
              _InfoRow(
                icon: Icons.verified_user,
                label: 'Giriş Yöntemi',
                value: _getMethodLabel(timesheet.checkInMethod!),
              ),
            _InfoRow(
              icon: Icons.info_outline,
              label: 'Durum',
              value: timesheet.durumEtiketi,
              valueColor: _getStatusColor(timesheet.status),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProjectInfo(todayState) {
    final project = todayState.currentProject;

    if (project == null) {
      return Card(
        elevation: 2,
        color: Colors.orange.shade50,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppBorderRadius.xl)),
        child: Padding(
          padding: const EdgeInsets.all(AppSpacing.lg),
          child: Row(
            children: [
              const Icon(Icons.warning, color: Colors.orange),
              const SizedBox(width: AppSpacing.md),
              Expanded(
                child: Text(
                  'Henüz bir projeye atanmadınız. Lütfen yöneticinizle iletişime geçin.',
                  style: AppTextStyles.bodyMedium,
                ),
              ),
            ],
          ),
        ),
      );
    }

    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppBorderRadius.xl)),
      child: Padding(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.business, color: AppColors.primary),
                const SizedBox(width: AppSpacing.sm),
                Text(
                  'Atandığınız Proje',
                  style: AppTextStyles.h3.copyWith(fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: AppSpacing.md),
            Container(
              padding: const EdgeInsets.all(AppSpacing.md),
              decoration: BoxDecoration(
                color: AppColors.primaryLight,
                borderRadius: BorderRadius.circular(AppBorderRadius.md),
              ),
              child: Row(
                children: [
                  const Icon(Icons.location_city, color: AppColors.primary, size: 32),
                  const SizedBox(width: AppSpacing.md),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          project.name,
                          style: AppTextStyles.h4.copyWith(fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Kod: ${project.projectCode}',
                          style: AppTextStyles.bodySmall.copyWith(color: AppColors.textSecondary),
                        ),
                      ],
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

  Widget _buildMethodSelector() {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppBorderRadius.xl)),
      child: Padding(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.qr_code, color: AppColors.primary),
                const SizedBox(width: AppSpacing.sm),
                Text(
                  'Giriş Yöntemi',
                  style: AppTextStyles.h3.copyWith(fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: AppSpacing.md),
            Wrap(
              spacing: AppSpacing.sm,
              runSpacing: AppSpacing.sm,
              children: [
                _buildMethodChip('gps', 'GPS', Icons.location_on),
                _buildMethodChip('qr', 'QR Kod', Icons.qr_code_scanner),
                _buildMethodChip('nfc', 'NFC', Icons.nfc),
                _buildMethodChip('biometric', 'Biyometrik', Icons.fingerprint),
                _buildMethodChip('manual', 'Manuel', Icons.touch_app),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMethodChip(String value, String label, IconData icon) {
    final isSelected = _selectedMethod == value;
    return FilterChip(
      selected: isSelected,
      label: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16),
          const SizedBox(width: 4),
          Text(label),
        ],
      ),
      onSelected: (selected) {
        setState(() {
          _selectedMethod = value;
        });
      },
      selectedColor: AppColors.primary.withValues(alpha: 0.2),
      checkmarkColor: AppColors.primary,
    );
  }

  Widget _buildNotesInput() {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppBorderRadius.xl)),
      child: Padding(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.note_add, color: AppColors.primary),
                const SizedBox(width: AppSpacing.sm),
                Text(
                  'Not (Opsiyonel)',
                  style: AppTextStyles.h3.copyWith(fontWeight: FontWeight.bold),
                ),
              ],
            ),
            const SizedBox(height: AppSpacing.md),
            TextField(
              controller: _notesController,
              decoration: InputDecoration(
                hintText: 'İsteğe bağlı not ekleyin...',
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(AppBorderRadius.md),
                ),
                contentPadding: const EdgeInsets.all(AppSpacing.md),
              ),
              maxLines: 3,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildClockInButton(todayState) {
    return SizedBox(
      width: double.infinity,
      height: 56,
      child: ElevatedButton.icon(
        onPressed: todayState.isLoading ? null : _onClockIn,
        icon: const Icon(Icons.login, size: 24),
        label: Text(
          todayState.isLoading ? 'İşleniyor...' : 'Giriş Yap',
          style: AppTextStyles.h3.copyWith(
            color: Colors.white,
            fontWeight: FontWeight.bold,
          ),
        ),
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primary,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppBorderRadius.xl),
          ),
          elevation: 4,
        ),
      ),
    );
  }

  Widget _buildClockOutButton(todayState) {
    return SizedBox(
      width: double.infinity,
      height: 56,
      child: ElevatedButton.icon(
        onPressed: todayState.isLoading ? null : _onClockOut,
        icon: const Icon(Icons.logout, size: 24),
        label: Text(
          todayState.isLoading ? 'İşleniyor...' : 'Çıkış Yap',
          style: AppTextStyles.h3.copyWith(
            color: Colors.white,
            fontWeight: FontWeight.bold,
          ),
        ),
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.red[600],
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppBorderRadius.xl),
          ),
          elevation: 4,
        ),
      ),
    );
  }

  Widget _buildCompletedMessage() {
    return Card(
      elevation: 2,
      color: Colors.green.shade50,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppBorderRadius.xl)),
      child: Padding(
        padding: const EdgeInsets.all(AppSpacing.lg),
        child: Column(
          children: [
            Icon(
              Icons.check_circle_outline,
              size: 64,
              color: Colors.green[600],
            ),
            const SizedBox(height: AppSpacing.md),
            Text(
              'Bugünkü Çalışma Tamamlandı',
              style: AppTextStyles.h3.copyWith(
                fontWeight: FontWeight.bold,
                color: Colors.green[800],
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppSpacing.sm),
            Text(
              'Çıkış işleminiz başarıyla kaydedildi. İyi günler!',
              style: AppTextStyles.bodyMedium.copyWith(
                color: Colors.green[700],
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  String _getMethodLabel(String method) {
    switch (method) {
      case 'manual':
        return 'Manuel';
      case 'qr':
        return 'QR Kod';
      case 'gps':
        return 'GPS';
      case 'nfc':
        return 'NFC';
      case 'biometric':
        return 'Biyometrik';
      case 'mobile_offline':
        return 'Mobil (Offline)';
      default:
        return method;
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'present':
        return Colors.green;
      case 'absent':
        return Colors.red;
      case 'leave':
        return Colors.orange;
      case 'holiday':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final Color? valueColor;

  const _InfoRow({
    required this.icon,
    required this.label,
    required this.value,
    this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: AppSpacing.xs),
      child: Row(
        children: [
          Icon(icon, size: 20, color: AppColors.textSecondary),
          const SizedBox(width: AppSpacing.sm),
          SizedBox(
            width: 120,
            child: Text(
              label,
              style: AppTextStyles.bodyMedium.copyWith(
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: AppTextStyles.bodyMedium.copyWith(
                color: valueColor ?? AppColors.textPrimary,
                fontWeight: valueColor != null ? FontWeight.bold : null,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
