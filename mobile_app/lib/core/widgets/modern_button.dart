import 'package:flutter/material.dart';
import '../constants/app_constants.dart';

enum ModernButtonType {
  primary,
  secondary,
  outline,
  text,
  gradient,
}

class ModernButton extends StatelessWidget {
  final String text;
  final VoidCallback? onPressed;
  final ModernButtonType type;
  final bool isLoading;
  final bool isFullWidth;
  final IconData? icon;
  final double? height;
  final List<Color>? gradientColors;

  const ModernButton({
    super.key,
    required this.text,
    this.onPressed,
    this.type = ModernButtonType.primary,
    this.isLoading = false,
    this.isFullWidth = true,
    this.icon,
    this.height = 56,
    this.gradientColors,
  });

  @override
  Widget build(BuildContext context) {
    if (type == ModernButtonType.gradient) {
      return _buildGradientButton();
    }

    return SizedBox(
      width: isFullWidth ? double.infinity : null,
      height: height,
      child: ElevatedButton(
        onPressed: isLoading ? null : onPressed,
        style: _getButtonStyle(),
        child: _buildButtonChild(),
      ),
    );
  }

  Widget _buildGradientButton() {
    return Container(
      width: isFullWidth ? double.infinity : null,
      height: height,
      decoration: BoxDecoration(
        gradient: isLoading
            ? null
            : LinearGradient(
                colors: gradientColors ?? AppColors.primaryGradient,
              ),
        borderRadius: BorderRadius.circular(AppBorderRadius.md),
        boxShadow: isLoading ? null : AppShadows.lg,
        color: isLoading ? AppColors.buttonDisabled : null,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: isLoading ? null : onPressed,
          borderRadius: BorderRadius.circular(AppBorderRadius.md),
          child: Center(
            child: _buildButtonChild(isGradient: true),
          ),
        ),
      ),
    );
  }

  ButtonStyle _getButtonStyle() {
    switch (type) {
      case ModernButtonType.primary:
        return ElevatedButton.styleFrom(
          backgroundColor: AppColors.primary,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppBorderRadius.md),
          ),
          elevation: 2,
        );
      case ModernButtonType.secondary:
        return ElevatedButton.styleFrom(
          backgroundColor: AppColors.greyLight,
          foregroundColor: AppColors.textPrimary,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppBorderRadius.md),
          ),
          elevation: 0,
        );
      case ModernButtonType.outline:
        return ElevatedButton.styleFrom(
          backgroundColor: Colors.transparent,
          foregroundColor: AppColors.primary,
          side: const BorderSide(color: AppColors.primary, width: 2),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppBorderRadius.md),
          ),
          elevation: 0,
        );
      case ModernButtonType.text:
        return ElevatedButton.styleFrom(
          backgroundColor: Colors.transparent,
          foregroundColor: AppColors.primary,
          elevation: 0,
        );
      default:
        return ElevatedButton.styleFrom();
    }
  }

  Widget _buildButtonChild({bool isGradient = false}) {
    if (isLoading) {
      return SizedBox(
        height: 24,
        width: 24,
        child: CircularProgressIndicator(
          strokeWidth: 2.5,
          valueColor: AlwaysStoppedAnimation<Color>(
            isGradient ? AppColors.primary : Colors.white,
          ),
        ),
      );
    }

    if (icon != null) {
      return Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 20),
          const SizedBox(width: AppSpacing.sm),
          Text(
            text,
            style: AppTextStyles.button.copyWith(
              fontSize: 16,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      );
    }

    return Text(
      text,
      style: AppTextStyles.button.copyWith(
        fontSize: 16,
        fontWeight: FontWeight.w600,
        color: isGradient ? Colors.white : null,
      ),
    );
  }
}
