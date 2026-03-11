from django.urls import path
from . import views

app_name = 'inventory'


urlpatterns = [
    path('status/', views.fuel_status, name='fuel_status'),
    path('delivery/record/', views.record_delivery, name='record_delivery'),
    path('price/update/<int:pk>/', views.update_price, name='update_price'),
]

