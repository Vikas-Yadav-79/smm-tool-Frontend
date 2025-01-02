import { Routes } from '@angular/router';
import { DashboardComponent } from './dashboard/dashboard.component';

export const routes: Routes = [
  { path: '', redirectTo: '/dashboard', pathMatch: 'full' },
  { path: 'dashboard', component: DashboardComponent },
  { path: 'settings', component: DashboardComponent }, // Placeholder
  { path: 'social-accounts', component: DashboardComponent }, // Placeholder
  { path: 'statistics', component: DashboardComponent }, // Placeholder
];
