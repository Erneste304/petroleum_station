from django.shortcuts import render, get_object_or_404, redirect
from .models import FuelType, Tank, Pump, FuelDelivery
from .forms import FuelDeliveryForm, FuelPriceForm
from users.decorators import role_required

@role_required('admin', 'accountant', 'staff', 'partner')
def fuel_status(request):
    fuel_types = FuelType.objects.all()
    tanks = Tank.objects.all()
    pumps = Pump.objects.all()
    return render(request, 'inventory/fuel_status.html', {
        'fuel_types': fuel_types,
        'tanks': tanks,
        'pumps': pumps
    })

@role_required('admin', 'staff')
def record_delivery(request):
    """Record a new fuel delivery, update tank stock."""
    if request.method == 'POST':
        form = FuelDeliveryForm(request.POST)
        if form.is_valid():
            delivery = form.save(commit=False)
            # Update tank stock
            tank = delivery.tank
            tank.current_stock += delivery.quantity
            if tank.current_stock > tank.capacity:
                tank.current_stock = tank.capacity
            tank.save(update_fields=['current_stock'])
            delivery.save()
            return redirect('inventory:fuel_status')
    else:
        form = FuelDeliveryForm()
    return render(request, 'inventory/delivery_form.html', {'form': form, 'title': 'Record Fuel Delivery'})

@role_required('admin', 'accountant')
def update_price(request, pk):
    """Update price per liter for a fuel type."""
    fuel = get_object_or_404(FuelType, pk=pk)
    if request.method == 'POST':
        form = FuelPriceForm(request.POST, instance=fuel)
        if form.is_valid():
            form.save()
            return redirect('inventory:fuel_status')
    else:
        form = FuelPriceForm(instance=fuel)
    return render(request, 'inventory/update_price_form.html', {'form': form, 'fuel': fuel})
