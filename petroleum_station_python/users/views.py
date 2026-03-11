from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.decorators import login_required
from django.contrib.auth import authenticate, login, logout
from stations.models import Station
from sales.models import Sale
from inventory.models import FuelType, Tank
from loyalty.models import LoyaltyRedemption
from services.models import CarWashService
from .models import User, StaffReport, Customer
from .forms import UserCreateForm, UserEditForm, ProfileUpdateForm
from .report_forms import StaffReportForm, ApprovalNoteForm, RejectionForm
from .decorators import role_required
from django.utils import timezone
from django.contrib import messages


def login_view(request):
    error = None
    if request.method == 'POST':
        username = request.POST.get('username')
        password = request.POST.get('password')
        user = authenticate(request, username=username, password=password)
        if user is not None:
            login(request, user)
            return redirect('users:dashboard')
        else:
            error = 'Invalid username or password. Please try again.'
    return render(request, 'users/login.html', {'error': error})


def logout_view(request):
    logout(request)
    return redirect('users:login')



def dashboard(request):
    stations_count = Station.objects.count()
    total_sales = Sale.objects.count()
    latest_sales = Sale.objects.order_by('-sale_date')[:5]
    fuel_types = FuelType.objects.all()
    tanks = Tank.objects.all()
    
    # Calculate percentages for tanks
    for tank in tanks:
        if tank.capacity > 0:
            tank.percentage = (tank.current_stock / tank.capacity) * 100
        else:
            tank.percentage = 0
    
    context = {
        'stations_count': stations_count,
        'total_sales': total_sales,
        'latest_sales': latest_sales,
        'fuel_types': fuel_types,
        'tanks': tanks,
    }

    if request.user.role == 'customer' and request.user.customer:
        customer = request.user.customer
        context['customer_sales'] = Sale.objects.filter(customer=customer).order_by('-sale_date')[:5]
        context['customer_redemptions'] = LoyaltyRedemption.objects.filter(customer=customer).order_by('-redeemed_date')[:5]
        context['available_services'] = CarWashService.objects.all()
        # Calculate some stats for the top row
        context['my_total_purchases'] = Sale.objects.filter(customer=customer).count()
        context['my_total_spent'] = sum(s.total_amount for s in Sale.objects.filter(customer=customer))
        # Points can be calculated or retrieved. For this example we'll show count of rewards available.
        from loyalty.models import LoyaltyReward
        context['rewards_count'] = LoyaltyReward.objects.count()

    return render(request, 'users/dashboard.html', context)

def user_list(request):
    users = User.objects.all()
    return render(request, 'users/user_list.html', {'users': users})

def user_create(request):
    if request.method == 'POST':
        form = UserCreateForm(request.POST)
        if form.is_valid():
            form.save()
            return redirect('users:user_list')
    else:
        form = UserCreateForm()
    return render(request, 'users/user_form.html', {'form': form, 'title': 'Create User'})

def user_update(request, pk):
    user = get_object_or_404(User, pk=pk)
    if request.method == 'POST':
        form = UserEditForm(request.POST, instance=user)
        if form.is_valid():
            form.save()
            return redirect('users:user_list')
    else:
        form = UserEditForm(instance=user)
    return render(request, 'users/user_form.html', {'form': form, 'title': f'Edit User: {user.username}'})

@login_required
def report_list(request):
    """
    List reports. 
    Admin sees all. 
    Accountant sees financial.
    Staff sees own.
    """
    if request.user.is_admin:
        reports = StaffReport.objects.all()
    elif request.user.is_accountant:
        reports = StaffReport.objects.filter(report_type='financial')
    else:
        reports = StaffReport.objects.filter(submitted_by=request.user)
        
    return render(request, 'users/report_list.html', {'reports': reports})

@login_required
def report_create(request):
    """Staff only creates reports"""
    if request.method == 'POST':
        form = StaffReportForm(request.POST)
        if form.is_valid():
            report = form.save(commit=False)
            report.submitted_by = request.user
            report.save()
            messages.success(request, 'Report submitted successfully.')
            return redirect('users:report_list')
    else:
        form = StaffReportForm()
    return render(request, 'users/report_form.html', {'form': form})

@login_required
@role_required('accountant', 'admin')
def report_accountant_approve(request, pk):
    report = get_object_or_404(StaffReport, pk=pk)
    if report.status != 'pending':
        messages.error(request, 'Report is not pending.')
        return redirect('users:report_list')
        
    if request.method == 'POST':
        form = ApprovalNoteForm(request.POST)
        if form.is_valid():
            report.accountant_approved = True
            report.accountant_approved_by = request.user
            report.accountant_approved_at = timezone.now()
            report.accountant_note = form.cleaned_data.get('note', '')
            report.status = 'accountant_approved'
            report.save()
            messages.success(request, 'Report operationally approved.')
            return redirect('users:report_list')
    else:
        form = ApprovalNoteForm()
        
    return render(request, 'users/report_approve.html', {
        'form': form, 
        'report': report,
        'title': 'Operational Approval (Accountant)',
        'action_url': 'users:report_accountant_approve'
    })

@login_required
@role_required('admin')
def report_admin_approve(request, pk):
    report = get_object_or_404(StaffReport, pk=pk)
    if report.status != 'accountant_approved' and report.report_type == 'financial':
        messages.error(request, 'Financial reports must be approved by accountant first.')
        return redirect('users:report_list')
        
    if request.method == 'POST':
        form = ApprovalNoteForm(request.POST)
        if form.is_valid():
            report.admin_approved = True
            report.admin_approved_by = request.user
            report.admin_approved_at = timezone.now()
            report.admin_note = form.cleaned_data.get('note', '')
            report.status = 'admin_approved'
            report.save()
            messages.success(request, 'Report finalized and financially approved.')
            return redirect('users:report_list')
    else:
        form = ApprovalNoteForm()
        
    return render(request, 'users/report_approve.html', {
        'form': form, 
        'report': report,
        'title': 'Final Financial Approval (Admin)',
        'action_url': 'users:report_admin_approve'
    })

@login_required
@role_required('admin', 'accountant')
def report_reject(request, pk):
    report = get_object_or_404(StaffReport, pk=pk)
    if report.status in ['admin_approved', 'rejected']:
        messages.error(request, 'Cannot reject this report.')
        return redirect('users:report_list')
        
    if request.method == 'POST':
        form = RejectionForm(request.POST)
        if form.is_valid():
            report.status = 'rejected'
            report.rejection_reason = form.cleaned_data.get('reason', '')
            report.save()
            messages.success(request, 'Report rejected.')
            return redirect('users:report_list')
    else:
        form = RejectionForm()
        
    return render(request, 'users/report_reject.html', {'form': form, 'report': report})

@login_required
def profile(request):
    if request.method == 'POST':
        form = ProfileUpdateForm(request.POST, instance=request.user)
        if form.is_valid():
            form.save()
            messages.success(request, 'Your profile has been updated successfully!')
            return redirect('users:profile')
    else:
        form = ProfileUpdateForm(instance=request.user)
    
    return render(request, 'users/profile.html', {'user': request.user, 'form': form})
