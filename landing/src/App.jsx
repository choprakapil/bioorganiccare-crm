import { useState, useEffect, useRef } from 'react'
import {
  Users, Calendar, FileText, IndianRupee, BarChart3, ShieldCheck,
  Clock, CheckCircle2, MessageSquare, Phone, MapPin, Stethoscope,
  ArrowRight, HeartPulse, Send, Zap, AlertCircle
} from 'lucide-react'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import './App.css'

gsap.registerPlugin(ScrollTrigger);

const VALID_TYPES = [
  "General Physician", "Dentist", "Dermatologist",
  "Pediatrician", "Gynecologist", "Orthopedic", "Other"
];

function App() {
  const [formData, setFormData] = useState({
    name: '', clinic_name: '', phone: '', whatsapp: '',
    city: '', practice_type: '', message: ''
  });

  const [errors, setErrors] = useState({});
  const [status, setStatus] = useState({ loading: false, success: false, error: null });

  const heroRef = useRef(null);

  useEffect(() => {
    const ctx = gsap.context(() => {
      // Hero Entrance
      gsap.from(".hero-animate", {
        y: 60, opacity: 0, duration: 1.2, stagger: 0.25, ease: "power4.out"
      });

      // Feature cards stagger
      gsap.fromTo(".feature-card",
        { y: 80, opacity: 0 },
        {
          scrollTrigger: {
            trigger: ".features-grid",
            start: "top 90%", // Reveal slightly earlier
          },
          y: 0,
          opacity: 1,
          duration: 1,
          stagger: 0.15,
          ease: "power3.out"
        }
      );

      // Reveal sections
      gsap.utils.toArray(".reveal-section").forEach((section) => {
        gsap.fromTo(section,
          { y: 60, opacity: 0 },
          {
            scrollTrigger: {
              trigger: section,
              start: "top 95%", // More sensitive trigger
            },
            y: 0,
            opacity: 1,
            duration: 1.2,
            ease: "power2.out"
          }
        );
      });

      // Stats counter simulation
      gsap.utils.toArray(".stat-number").forEach((stat) => {
        gsap.from(stat, {
          scrollTrigger: {
            trigger: stat,
            start: "top 95%",
          },
          innerText: 0,
          duration: 2,
          snap: { innerText: 1 },
          ease: "power1.out"
        });
      });

      // Force a refresh after a small delay to catch layout shifts
      setTimeout(() => {
        ScrollTrigger.refresh();
      }, 500);

    }, heroRef);

    return () => ctx.revert();
  }, []);

  const validate = () => {
    let newErrors = {};
    if (formData.name.trim().length < 3) newErrors.name = "Full Name is required (min 3 chars)";
    if (!formData.clinic_name.trim()) newErrors.clinic_name = "Clinic Name is required";
    if (!/^[6-9]\d{9}$/.test(formData.phone)) newErrors.phone = "Enter a valid 10-digit Indian mobile number";
    if (!formData.city.trim()) newErrors.city = "City name is required";
    if (!formData.practice_type) newErrors.practice_type = "Please select your practice type";

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    if (errors[name]) setErrors(prev => ({ ...prev, [name]: null }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validate()) {
      const firstError = document.querySelector('.error-msg');
      if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    setStatus({ loading: true, success: false, error: null });

    // Resolve API endpoint for both dev and production without relying on localhost in prod
    const API_URL = import.meta.env.DEV
      // Local development: hit the Laravel app directly (can be overridden via VITE_API_URL)
      ? (import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/enquiries')
      // Production: always go through the deployed API prefix on the same origin
      : '/api/enquiries';

    try {
      const response = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      });

      const data = await response.json();

      if (data.success) {
        setStatus({ loading: false, success: true, error: null });
        setFormData({ name: '', clinic_name: '', phone: '', whatsapp: '', city: '', practice_type: '', message: '' });
      } else {
        setStatus({ loading: false, success: false, error: data.error });
      }
    } catch (err) {
      console.error(err);
      setStatus({ loading: false, success: false, error: 'Connection Failed. Please ensure your local backend is running.' });
    }
  };

  const whatsappMsg = encodeURIComponent("Hi, I’m interested in the free 1-month CRM trial for my clinic. I’d like to book a demo.");
  const whatsappUrl = `https://wa.me/917015312155?text=${whatsappMsg}`;

  return (
    <div className="app" ref={heroRef}>
      {/* Schema.org JSON-LD */}
      <script type="application/ld+json">
        {JSON.stringify({
          "@context": "https://schema.org",
          "@type": "SoftwareApplication",
          "name": "ClinicPro CRM",
          "operatingSystem": "Web",
          "applicationCategory": "HealthApplication",
          "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "INR"
          },
          "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.9",
            "reviewCount": "520"
          }
        })}
      </script>

      {/* Navbar */}
      <nav className="glass-nav">
        <div className="container" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
            <div style={{ background: 'var(--primary)', padding: '0.6rem', borderRadius: '1rem', color: 'white' }}>
              <Zap size={24} fill="currentColor" />
            </div>
            <span style={{ fontSize: '1.6rem', fontWeight: '900', color: 'var(--dark)', letterSpacing: '-0.04em' }}>ClinicPro<span style={{ color: 'var(--primary)' }}>CRM</span></span>
          </div>
          <div style={{ display: 'flex', gap: '2.5rem', alignItems: 'center' }}>
            <a href="#features" className="nav-link hide-mobile" style={{ fontWeight: 600 }}>Features</a>
            <a href="#enquiry" className="btn btn-primary">Start Free Trial <ArrowRight size={18} /></a>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <header className="hero section container">
        <div className="hero-content">
          <span className="badge hero-animate">🚀 The Future of Practice Management</span>
          <h1 className="hero-animate">Smart Clinics Start <span style={{ color: 'var(--primary)' }}>Here.</span></h1>
          <p className="hero-animate">Tired of manual prescriptions and messy billing? Join 500+ Indian doctors using ClinicPro to automate their practice and focus on what they do best: Healing.</p>
          <div className="hero-animate" style={{ display: 'flex', gap: '1.5rem', justifyContent: 'center', flexWrap: 'wrap' }}>
            <a href="#enquiry" className="btn btn-primary" style={{ padding: '1.25rem 3rem' }}>Get 1 Month Free Access</a>
            <a href={whatsappUrl} target="_blank" className="btn btn-whatsapp" style={{ padding: '1.25rem 3rem' }}>
              <MessageSquare size={22} /> Book Free Live Demo
            </a>
          </div>
        </div>
      </header>

      {/* Stats Section */}
      <section className="reveal-section container text-center" style={{ paddingBottom: '8rem' }}>
        <div className="grid" style={{ gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '4rem' }}>
          <div><h2 className="stat-number" style={{ fontSize: '3.5rem', color: 'var(--primary)' }}>520+</h2><p style={{ fontWeight: 700 }}>Active Doctors</p></div>
          <div><h2 className="stat-number" style={{ fontSize: '3.5rem', color: 'var(--primary)' }}>65000+</h2><p style={{ fontWeight: 700 }}>Prescriptions Created</p></div>
          <div><h2 className="stat-number" style={{ fontSize: '3.5rem', color: 'var(--primary)' }}>92%</h2><p style={{ fontWeight: 700 }}>Efficiency Boost</p></div>
          <div><h2 className="stat-number" style={{ fontSize: '3.5rem', color: 'var(--primary)' }}>24/7</h2><p style={{ fontWeight: 700 }}>Cloud Availability</p></div>
        </div>
      </section>

      {/* Pain Points */}
      <section className="section bg-light reveal-section">
        <div className="container">
          <div style={{ maxWidth: '850px', margin: '0 auto', textAlign: 'center', marginBottom: '5rem' }}>
            <span style={{ color: 'var(--primary)', fontWeight: '900', textTransform: 'uppercase', fontSize: '0.9rem' }}>The Problem</span>
            <h2 style={{ fontSize: '3.5rem', marginTop: '1rem' }}>Paper Records Are <span style={{ color: 'var(--danger)' }}>Killing</span> Your Productivity.</h2>
          </div>
          <div className="grid features-grid">
            <div className="feature-card">
              <div className="feature-icon"><Clock size={36} /></div>
              <h3>Wasted Patient Time</h3>
              <p>Searching for old files while patients wait? Digitize records and find any patient history in 1.5 seconds.</p>
            </div>
            <div className="feature-card">
              <div className="feature-icon"><IndianRupee size={36} /></div>
              <h3>Missing Revenue</h3>
              <p>Untracked follow-ups and lost bills mean you're losing money every day. Our system plugs every leak.</p>
            </div>
            <div className="feature-card">
              <div className="feature-icon"><AlertCircle size={36} /></div>
              <h3>Compliance Risks</h3>
              <p>Handwritten notes are hard to read and easy to lose. Professional digital records protect your practice.</p>
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section id="features" className="section reveal-section">
        <div className="container">
          <div style={{ textAlign: 'center', marginBottom: '6rem' }}>
            <span style={{ color: 'var(--primary)', fontWeight: '900', textTransform: 'uppercase', fontSize: '0.9rem' }}>The Solution</span>
            <h2 style={{ fontSize: '3.5rem', marginTop: '1rem' }}>Built for Scale, <span style={{ color: 'var(--primary)' }}>Designed for Speed.</span></h2>
          </div>

          <div className="grid features-grid">
            <div className="feature-card">
              <div className="feature-icon"><Calendar size={32} /></div>
              <h3>Smart Appointment Desk</h3>
              <p>Manage walk-ins and bookings on a visual drag-and-drop calendar. No more double bookings.</p>
            </div>
            <div className="feature-card">
              <div className="feature-icon"><FileText size={32} /></div>
              <h3>1-Click Prescriptions</h3>
              <p>Modern templates that learn your style. Print professional Rx with your clinic branding instantly.</p>
            </div>
            <div className="feature-card">
              <div className="feature-icon"><Users size={32} /></div>
              <h3>Patient Portal</h3>
              <p>Let patients download their reports and bills on their phone. Saves your staff's time.</p>
            </div>
            <div className="feature-card">
              <div className="feature-icon"><BarChart3 size={32} /></div>
              <h3>Financial Intelligence</h3>
              <p>Daily, weekly, and monthly revenue analytics. Know your most profitable treatments.</p>
            </div>
            <div className="feature-card">
              <div className="feature-icon"><ShieldCheck size={32} /></div>
              <h3>HIPAA Compliant</h3>
              <p>Bank-grade security ensures your patient data is private and encrypted at all times.</p>
            </div>
            <div className="feature-card">
              <div className="feature-icon"><Zap size={32} fill="currentColor" /></div>
              <h3>WhatsApp Automation</h3>
              <p>Send appointment updates and digital invoices directly to WhatsApp. 98% open rates.</p>
            </div>
          </div>
        </div>
      </section>

      {/* CTA / Enquiry Form */}
      <section id="enquiry" className="section bg-light reveal-section" style={{ overflow: 'hidden' }}>
        <div className="container">
          <div className="grid" style={{ gridTemplateColumns: '1.2fr 1fr', gap: '5rem', alignItems: 'center' }}>
            <div>
              <span className="badge">Special Limited Offer</span>
              <h2 style={{ fontSize: '4rem', marginBottom: '2.5rem' }}>Start Your <span style={{ color: 'var(--primary)' }}>Digital Era</span> Today.</h2>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '2rem' }}>
                <div style={{ display: 'flex', gap: '1.25rem', alignItems: 'flex-start' }}>
                  <div style={{ background: '#dcfce7', padding: '0.4rem', borderRadius: '50%' }}><CheckCircle2 color="var(--success)" size={24} /></div>
                  <div>
                    <h4 style={{ marginBottom: '0.25rem' }}>100% Free for 30 Days</h4>
                    <p>Experience the full pro version without paying a single Rupee.</p>
                  </div>
                </div>
                <div style={{ display: 'flex', gap: '1.25rem', alignItems: 'flex-start' }}>
                  <div style={{ background: '#dcfce7', padding: '0.4rem', borderRadius: '50%' }}><CheckCircle2 color="var(--success)" size={24} /></div>
                  <div>
                    <h4 style={{ marginBottom: '0.25rem' }}>Personalized Demo</h4>
                    <p>Our experts will walk you through the system on a Zoom call.</p>
                  </div>
                </div>
                <div style={{ display: 'flex', gap: '1.25rem', alignItems: 'flex-start' }}>
                  <div style={{ background: '#dcfce7', padding: '0.4rem', borderRadius: '50%' }}><CheckCircle2 color="var(--success)" size={24} /></div>
                  <div>
                    <h4 style={{ marginBottom: '0.25rem' }}>Data Migration Support</h4>
                    <p>We'll help you move your existing records into the new dashboard.</p>
                  </div>
                </div>
              </div>
            </div>

            <div className="form-container">
              {status.success ? (
                <div className="text-center" style={{ padding: '2rem' }}>
                  <div style={{ width: '100px', height: '100px', background: '#f0fdf4', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', margin: '0 auto 2.5rem' }}>
                    <HeartPulse size={50} color="var(--success)" />
                  </div>
                  <h2 style={{ color: 'var(--success)', marginBottom: '1rem' }}>Request Sent!</h2>
                  <p style={{ fontSize: '1.25rem', marginBottom: '2.5rem' }}>We've received your request. An expert will reach out to schedule your 30-day trial setup within 24 hours.</p>
                  <a href={whatsappUrl} className="btn btn-whatsapp" style={{ width: '100%', padding: '1.5rem' }}>
                    <MessageSquare size={24} /> Instant Update on WhatsApp
                  </a>
                </div>
              ) : (
                <form onSubmit={handleSubmit} noValidate>
                  <div className="form-group">
                    <label>Doctor's Full Name *</label>
                    <input type="text" name="name" value={formData.name} onChange={handleChange} placeholder="Dr. Sandeep Sehgal" />
                    {errors.name && <div className="error-msg"><AlertCircle size={14} /> {errors.name}</div>}
                  </div>
                  <div className="form-group">
                    <label>Clinic / Hospital Name *</label>
                    <input type="text" name="clinic_name" value={formData.clinic_name} onChange={handleChange} placeholder="Karma Ayurveda" />
                    {errors.clinic_name && <div className="error-msg"><AlertCircle size={14} /> {errors.clinic_name}</div>}
                  </div>
                  <div className="grid" style={{ gridTemplateColumns: '1.1fr 0.9fr', gap: '1.5rem' }}>
                    <div className="form-group">
                      <label>Phone Number *</label>
                      <input type="tel" name="phone" value={formData.phone} onChange={handleChange} placeholder="98765 43210" />
                      {errors.phone && <div className="error-msg"><AlertCircle size={14} /> {errors.phone}</div>}
                    </div>
                    <div className="form-group">
                      <label>WhatsApp</label>
                      <input type="tel" name="whatsapp" value={formData.whatsapp} onChange={handleChange} placeholder="Optional" />
                    </div>
                  </div>
                  <div className="grid" style={{ gridTemplateColumns: '1fr 1fr', gap: '1.5rem' }}>
                    <div className="form-group">
                      <label>City *</label>
                      <input type="text" name="city" value={formData.city} onChange={handleChange} placeholder="e.g. Delhi" />
                      {errors.city && <div className="error-msg"><AlertCircle size={14} /> {errors.city}</div>}
                    </div>
                    <div className="form-group">
                      <label>Specialization *</label>
                      <select name="practice_type" value={formData.practice_type} onChange={handleChange}>
                        <option value="">Choose...</option>
                        {VALID_TYPES.map(t => <option key={t} value={t}>{t}</option>)}
                      </select>
                      {errors.practice_type && <div className="error-msg"><AlertCircle size={14} /> {errors.practice_type}</div>}
                    </div>
                  </div>
                  <div className="form-group">
                    <label>Your Message / Requirements</label>
                    <textarea name="message" value={formData.message} onChange={handleChange} rows="2" placeholder="Tell us about your clinic..."></textarea>
                  </div>
                  {status.error && <div style={{ color: 'var(--danger)', marginBottom: '1.5rem', background: '#fef2f2', padding: '1.25rem', borderRadius: '1rem', fontWeight: 700, fontSize: '0.9rem' }}>❌ {status.error}</div>}
                  <button type="submit" className="btn btn-primary" style={{ width: '100%', padding: '1.4rem', fontSize: '1.1rem' }} disabled={status.loading}>
                    {status.loading ? <Zap size={22} className="spin" /> : 'Start My 1-Month Free Trial Now'}
                  </button>
                </form>
              )}
            </div>
          </div>
        </div>
      </section>

      {/* Floating WhatsApp Widget */}
      <a href={whatsappUrl} target="_blank" className="floating-wa" title="Message us for a Demo">
        <MessageSquare size={36} fill="white" />
      </a>

      {/* Footer */}
      <footer style={{ backgroundColor: '#0f172a', color: 'white', padding: '8rem 0 4rem' }}>
        <div className="container">
          <div className="grid" style={{ gridTemplateColumns: '1.5fr 1fr 1fr', gap: '6rem', marginBottom: '6rem' }}>
            <div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem', marginBottom: '2rem' }}>
                <div style={{ background: 'var(--primary)', padding: '0.4rem', borderRadius: '0.75rem' }}><Zap size={20} fill="white" /></div>
                <h3 style={{ color: 'white', fontSize: '1.75rem' }}>ClinicPro CRM</h3>
              </div>
              <p style={{ opacity: 0.6, fontSize: '1rem', maxWidth: '400px' }}>Helping Indian clinics digitize, automate, and grow. Trusted by leading doctors across the country. Built for performance, security, and scale.</p>
            </div>
            <div>
              <h4 style={{ color: 'white', marginBottom: '2rem', fontSize: '1.25rem' }}>Product</h4>
              <ul style={{ display: 'flex', flexDirection: 'column', gap: '1rem', opacity: 0.7, listStyle: 'none' }}>
                <li><a href="#features">Key Features</a></li>
                <li><a href="#enquiry">Pricing Model</a></li>
                <li><a href="#enquiry">Book a Demo</a></li>
                <li><a href="#">Security Hub</a></li>
              </ul>
            </div>
            <div>
              <h4 style={{ color: 'white', marginBottom: '2rem', fontSize: '1.25rem' }}>Support</h4>
              <ul style={{ display: 'flex', flexDirection: 'column', gap: '1rem', opacity: 0.7, listStyle: 'none' }}>
                <li><a href="tel:+917015312155"><Phone size={16} /> +91 70153 12155</a></li>
                <li><a href="https://wa.me/917015312155"><MessageSquare size={16} /> WhatsApp Support</a></li>
                <li><a href="#"><MapPin size={16} /> Delhi, India</a></li>
              </ul>
            </div>
          </div>
          <div style={{ borderTop: '1px solid rgba(255,255,255,0.1)', paddingTop: '3rem', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '2rem' }}>
            <p style={{ fontSize: '0.9rem', opacity: 0.4 }}>© {new Date().getFullYear()} ClinicPro CRM. High-Performance Practice Management Suite.</p>
            <div style={{ display: 'flex', gap: '3rem', opacity: 0.4, fontSize: '0.9rem' }}>
              <a href="#">Privacy Policy</a>
              <a href="#">Terms of Use</a>
              <a href="#">Cookie Policy</a>
            </div>
          </div>
        </div>
      </footer>
    </div>
  )
}

export default App
