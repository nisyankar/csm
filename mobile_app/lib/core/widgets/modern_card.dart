import 'package:flutter/material.dart';
import '../constants/app_constants.dart';

class ModernCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry? padding;
  final VoidCallback? onTap;
  final Color? backgroundColor;
  final List<BoxShadow>? boxShadow;
  final BorderRadius? borderRadius;
  final Gradient? gradient;

  const ModernCard({
    super.key,
    required this.child,
    this.padding,
    this.onTap,
    this.backgroundColor,
    this.boxShadow,
    this.borderRadius,
    this.gradient,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: gradient == null ? (backgroundColor ?? Colors.white) : null,
        gradient: gradient,
        borderRadius: borderRadius ?? BorderRadius.circular(AppBorderRadius.xl),
        boxShadow: boxShadow ?? AppShadows.md,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: borderRadius ?? BorderRadius.circular(AppBorderRadius.xl),
          child: Padding(
            padding: padding ?? const EdgeInsets.all(AppSpacing.lg),
            child: child,
          ),
        ),
      ),
    );
  }
}

class ModernGradientCard extends StatelessWidget {
  final Widget child;
  final List<Color> gradientColors;
  final EdgeInsetsGeometry? padding;
  final VoidCallback? onTap;

  const ModernGradientCard({
    super.key,
    required this.child,
    required this.gradientColors,
    this.padding,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ModernCard(
      gradient: LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: gradientColors,
      ),
      padding: padding,
      onTap: onTap,
      child: child,
    );
  }
}