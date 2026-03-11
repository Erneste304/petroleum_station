from django.shortcuts import render, redirect
from .models import Sale
from .forms import SaleForm
from users.decorators import role_required

@role_required('admin', 'accountant', 'staff', 'receptionist')
def sale_list(request):
    sales = Sale.objects.all().order_by('-sale_date')
    return render(request, 'sales/sale_list.html', {'sales': sales})

@role_required('admin', 'accountant', 'staff', 'receptionist')
def sale_create(request):
    if request.method == 'POST':
        form = SaleForm(request.POST)
        if form.is_valid():
            form.save()
            return redirect('users:dashboard')
    else:
        form = SaleForm()
    return render(request, 'sales/sale_form.html', {'form': form})
