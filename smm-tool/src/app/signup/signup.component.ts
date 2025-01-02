import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-signup',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './signup.component.html',
  styleUrls: ['./signup.component.css']
})
export class SignupComponent {
  name: string = '';
  email: string = '';
  password: string = '';

  onSignupSubmit() {
    console.log('Signup Form submitted');
    console.log('Name:', this.name);
    console.log('Email:', this.email);
    console.log('Password:', this.password);
  }
}
