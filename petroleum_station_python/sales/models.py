from django.db import models
from users.models import Customer
from stations.models import Employee
from inventory.models import Pump

class Sale(models.Model):
    customer = models.ForeignKey(Customer, on_delete=models.SET_NULL, null=True, blank=True)
    employee = models.ForeignKey(Employee, on_delete=models.CASCADE)
    pump = models.ForeignKey(Pump, on_delete=models.CASCADE)
    quantity = models.DecimalField(max_digits=10, decimal_places=2)
    total_amount = models.DecimalField(max_digits=10, decimal_places=2)
    sale_date = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        return f"Sale {self.id} - {self.total_amount} RWF"

class Payment(models.Model):
    PAYMENT_METHODS = (
        ('cash', 'Cash'),
        ('momo', 'Mobile Money'),
        ('card', 'Card'),
    )
    sale = models.ForeignKey(Sale, on_delete=models.CASCADE)
    payment_method = models.CharField(max_length=20, choices=PAYMENT_METHODS)
    amount = models.DecimalField(max_digits=10, decimal_places=2)
    payment_date = models.DateTimeField(auto_now_add=True)
    status = models.CharField(max_length=20, default='Paid')

    def __str__(self):
        return f"Payment for Sale {self.sale.id} - {self.amount} RWF"
