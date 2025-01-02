import { Component } from '@angular/core';
import { Routes } from '@angular/router';
import { DashboardComponent } from './dashboard/dashboard.component';
import { LoginComponent } from './login/login.component';
import { SignupComponent } from './signup/signup.component';
import { RouterModule } from '@angular/router';

// Define routes for the standalone app
export const routes: Routes = [
  { path: '', redirectTo: '/login', pathMatch: 'full' },
  { path: 'dashboard', component: DashboardComponent },
  { path: 'login', component: LoginComponent },
  { path: 'signup', component: SignupComponent },
];

@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterModule], // Import RouterModule for routing
  template: `<router-outlet></router-outlet>`, // Use router outlet to display routed components
})
export class AppRoutingComponent {}
