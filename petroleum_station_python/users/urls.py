from django.urls import path
from . import views


app_name = 'users'


urlpatterns = [
    path('login/', views.login_view, name='login'),
    path('logout/', views.logout_view, name='logout'),
    path('dashboard/', views.dashboard, name='dashboard'),
    path('users/', views.user_list, name='user_list'),
    path('users/create/', views.user_create, name='user_create'),
    path('users/<int:pk>/edit/', views.user_update, name='user_update'),
    path('reports/', views.report_list, name='report_list'),
    path('reports/create/', views.report_create, name='report_create'),
    path('reports/<int:pk>/approve/accountant/', views.report_accountant_approve, name='report_accountant_approve'),
    path('reports/<int:pk>/approve/admin/', views.report_admin_approve, name='report_admin_approve'),
    path('reports/<int:pk>/reject/', views.report_reject, name='report_reject'),
    path('profile/', views.profile, name='profile'),
    path('messages/report-issue/<int:sale_id>/', views.report_issue, name='report_issue'),
    path('messages/inbox/', views.inbox, name='inbox'),
    path('audit-logs/', views.audit_logs, name='audit_logs'),
    path('', views.dashboard, name='home'),
]




