from django.shortcuts import render
from django.contrib.auth.decorators import login_required

def dashboard(request):
    # To be populated with real data from models later
    return render(request, 'users/dashboard.html')
