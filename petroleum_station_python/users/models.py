from django.db import models
from django.contrib.auth.models import AbstractUser

class Customer(models.Model):
    customer_id = models.AutoField(primary_key=True)
    name = models.CharField(max_length=100)
    phone = models.CharField(max_length=20, blank=True, null=True)
    vehicle_plate = models.CharField(max_length=20, blank=True, null=True)

    class Meta:
        db_table = 'customer'
        managed = False

    def __str__(self):
        return self.name


class User(AbstractUser):
    ROLE_CHOICES = (
        ('admin', 'Admin'),
        ('staff', 'Staff Member'),
        ('accountant', 'Accountant'),
        ('receptionist', 'Receptionist'),
        ('partner', 'Partner'),
        ('customer', 'Customer'),
    )

    role = models.CharField(max_length=50, choices=ROLE_CHOICES, default='customer')
    customer = models.ForeignKey(Customer, on_delete=models.SET_NULL, null=True, blank=True)

    def __str__(self):
        return f"{self.username} ({self.role})"

    @property
    def is_admin(self):
        return self.role == 'admin' or self.is_superuser

    @property
    def is_accountant(self):
        return self.role == 'accountant'

    @property
    def is_staff_member(self):
        return self.role == 'staff'


class StaffReport(models.Model):
    REPORT_TYPE_CHOICES = (
        ('operational', 'Operational'),
        ('financial', 'Financial'),
        ('maintenance', 'Maintenance'),
        ('incident', 'Incident Report'),
    )
    STATUS_CHOICES = (
        ('pending', 'Pending Review'),
        ('accountant_approved', 'Accountant Approved'),
        ('admin_approved', 'Admin Approved (Final)'),
        ('rejected', 'Rejected'),
    )

    title = models.CharField(max_length=200)
    description = models.TextField()
    report_type = models.CharField(max_length=30, choices=REPORT_TYPE_CHOICES, default='operational')
    submitted_by = models.ForeignKey(
        User, on_delete=models.CASCADE, related_name='submitted_reports'
    )
    submitted_at = models.DateTimeField(auto_now_add=True)
    status = models.CharField(max_length=30, choices=STATUS_CHOICES, default='pending')

    # Stage 1: Accountant approval
    accountant_approved = models.BooleanField(default=False)
    accountant_approved_by = models.ForeignKey(
        User, on_delete=models.SET_NULL, null=True, blank=True, related_name='accountant_approvals'
    )
    accountant_approved_at = models.DateTimeField(null=True, blank=True)
    accountant_note = models.TextField(blank=True, null=True)

    # Stage 2: Admin final approval
    admin_approved = models.BooleanField(default=False)
    admin_approved_by = models.ForeignKey(
        User, on_delete=models.SET_NULL, null=True, blank=True, related_name='admin_approvals'
    )
    admin_approved_at = models.DateTimeField(null=True, blank=True)
    admin_note = models.TextField(blank=True, null=True)

    # Rejection
    rejection_reason = models.TextField(blank=True, null=True)

    class Meta:
        ordering = ['-submitted_at']

    def __str__(self):
        return f"[{self.get_report_type_display()}] {self.title} — {self.get_status_display()}"
