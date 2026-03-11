from django.db import models
from django.contrib.auth.models import AbstractUser

class Customer(models.Model):
    name = models.CharField(max_length=100)
    phone = models.CharField(max_length=20, blank=True, null=True)
    vehicle_plate = models.CharField(max_length=20, blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        return self.name

class User(AbstractUser):
    ROLE_CHOICES = (
        ('admin', 'Admin'),
        ('accountant', 'Accountant'),
        ('receptionist', 'Receptionist'),
        ('partner', 'Partner'),
        ('customer', 'Customer'),
    )
    role = models.CharField(max_length=50, choices=ROLE_CHOICES, default='customer')
    customer = models.ForeignKey(Customer, on_delete=models.SET_NULL, null=True, blank=True)

    def __str__(self):
        return f"{self.username} ({self.role})"
