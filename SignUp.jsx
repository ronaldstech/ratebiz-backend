import React, { useState } from 'react';
import { Mail, Lock, User, Building2, UserPlus, ArrowRight, Eye, EyeOff, Check, Phone, MapPin, FileText, Tag } from 'lucide-react';
import { Link, useNavigate } from 'react-router-dom';
import { motion, AnimatePresence } from 'framer-motion';

const SignUp = () => {
    const navigate = useNavigate();
    const [role, setRole] = useState('user'); // 'user' or 'business'
    const [isLoading, setIsLoading] = useState(false);
    const [showPassword, setShowPassword] = useState(false);

    const [formData, setFormData] = useState({
        name: '',
        email: '',
        password: '',
        phone: '',
        // Business specific
        businessName: '',
        category: '',
        location: '',
        description: ''
    });

    const [errors, setErrors] = useState({});

    const handleInputChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
        // Clear error when user types
        if (errors[name]) {
            setErrors(prev => ({
                ...prev,
                [name]: ''
            }));
        }
    };

    const validateForm = () => {
        const newErrors = {};
        if (!formData.name.trim()) newErrors.name = 'Name is required';
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!formData.email.trim()) {
            newErrors.email = 'Email is required';
        } else if (!emailRegex.test(formData.email)) {
            newErrors.email = 'Invalid email address';
        }

        if (!formData.password) {
            newErrors.password = 'Password is required';
        } else if (formData.password.length < 8) {
            newErrors.password = 'Password must be at least 8 characters';
        }

        if (role === 'business') {
            if (!formData.businessName.trim()) newErrors.businessName = 'Business Name is required';
            if (!formData.category) newErrors.category = 'Category is required';
            if (!formData.location.trim()) newErrors.location = 'Location is required';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        if (!validateForm()) return;

        setIsLoading(true);

        const payload = {
            name: formData.name,
            email: formData.email,
            password: formData.password,
            phone: formData.phone,
            role: role,
            ...(role === 'business' && { 
                businessName: formData.businessName,
                category: formData.category,
                location: formData.location,
                description: formData.description
            })
        };

        try {
            const response = await fetch('https://unimarket-mw.com/ratebiz/api/auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok) {
                console.log('Registration successful:', data);
                // Save token if needed, or just redirect
                alert("Account created successfully!");
                navigate('/login');
            } else {
                console.error('Registration failed:', data);
                setErrors(prev => ({
                    ...prev,
                    submit: data.message || data.error || "Registration failed."
                }));
            }
        } catch (error) {
            console.error('Network error:', error);
            setErrors(prev => ({
                ...prev,
                submit: "Network error. Please try again later."
            }));
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div style={{
            minHeight: '100vh',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            padding: '20px'
        }}>
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6 }}
                className="glass-card"
                style={{
                    width: '100%',
                    maxWidth: '600px', // Slightly wider for better layout
                    padding: '40px',
                    position: 'relative'
                }}
            >
                <div style={{ textAlign: 'center', marginBottom: '32px' }}>
                    <h1 className="gradient-text" style={{ fontSize: '2.5rem', fontWeight: '800', marginBottom: '8px' }}>
                        Create Account
                    </h1>
                    <p style={{ color: 'var(--text-muted)' }}>Join RateBiz to start reviewing or growing your business.</p>
                </div>

                {errors.submit && (
                    <div className="error-banner" style={{
                        background: 'rgba(255, 59, 48, 0.1)',
                        border: '1px solid var(--error)',
                        color: 'var(--error)',
                        padding: '12px',
                        borderRadius: '8px',
                        marginBottom: '20px',
                        textAlign: 'center'
                    }}>
                        {errors.submit}
                    </div>
                )}

                <div className="role-selector">
                    <motion.div
                        className="role-selector-bg"
                        layoutId="activeRole"
                        initial={false}
                        animate={{
                            left: role === 'user' ? '4px' : 'calc(50% + 2px)'
                        }}
                        transition={{ type: "spring", stiffness: 300, damping: 30 }}
                    />
                    <button
                        onClick={() => setRole('user')}
                        className={`role-btn ${role === 'user' ? 'active' : ''}`}
                    >
                        <User size={18} /> User
                    </button>
                    <button
                        onClick={() => setRole('business')}
                        className={`role-btn ${role === 'business' ? 'active' : ''}`}
                    >
                        <Building2 size={18} /> Business
                    </button>
                </div>

                <form onSubmit={handleSubmit}>
                    <AnimatePresence mode="wait">
                        <motion.div
                            key={role}
                            initial={{ opacity: 0, x: role === 'user' ? -10 : 10 }}
                            animate={{ opacity: 1, x: 0 }}
                            exit={{ opacity: 0, x: role === 'user' ? 10 : -10 }}
                            transition={{ duration: 0.3 }}
                        >
                            <div className="form-grid" style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                                {/* Full Name / Owner Name */}
                                <div className="input-group" style={{ gridColumn: '1 / -1' }}>
                                    <label>{role === 'user' ? 'Full Name' : 'Business Owner Name'}</label>
                                    <div className="input-wrapper">
                                        <User size={18} className="input-icon" />
                                        <input
                                            type="text"
                                            name="name"
                                            placeholder="John Doe"
                                            value={formData.name}
                                            onChange={handleInputChange}
                                            className={errors.name ? 'error' : ''}
                                        />
                                    </div>
                                    {errors.name && <span className="error-text">{errors.name}</span>}
                                </div>

                                {/* Business Name & Category (Business Only) */}
                                {role === 'business' && (
                                    <>
                                        <div className="input-group" style={{ gridColumn: '1 / -1' }}>
                                            <label>Business Name</label>
                                            <div className="input-wrapper">
                                                <Building2 size={18} className="input-icon" />
                                                <input
                                                    type="text"
                                                    name="businessName"
                                                    placeholder="Tech Solutions Inc"
                                                    value={formData.businessName}
                                                    onChange={handleInputChange}
                                                    className={errors.businessName ? 'error' : ''}
                                                />
                                            </div>
                                            {errors.businessName && <span className="error-text">{errors.businessName}</span>}
                                        </div>
                                        
                                        <div className="input-group">
                                            <label>Category</label>
                                            <div className="input-wrapper">
                                                <Tag size={18} className="input-icon" />
                                                <select
                                                    name="category"
                                                    value={formData.category}
                                                    onChange={handleInputChange}
                                                    className={errors.category ? 'error' : ''}
                                                    style={{ width: '100%', background: 'transparent', border: 'none', outline: 'none', paddingLeft: '40px' }}
                                                >
                                                    <option value="">Select...</option>
                                                    <option value="Retail">Retail</option>
                                                    <option value="Food & Dining">Food & Dining</option>
                                                    <option value="Service">Service</option>
                                                    <option value="Technology">Technology</option>
                                                    <option value="Healthcare">Healthcare</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            {errors.category && <span className="error-text">{errors.category}</span>}
                                        </div>
                                    </>
                                )}

                                {/* Phone & Location */}
                                <div className="input-group" style={{ gridColumn: role === 'business' ? 'span 1' : '1 / -1' }}>
                                    <label>Phone Number</label>
                                    <div className="input-wrapper">
                                        <Phone size={18} className="input-icon" />
                                        <input
                                            type="tel"
                                            name="phone"
                                            placeholder="+1 234 567 890"
                                            value={formData.phone}
                                            onChange={handleInputChange}
                                        />
                                    </div>
                                </div>

                                {role === 'business' && (
                                    <div className="input-group" style={{ gridColumn: '1 / -1' }}>
                                        <label>Location / Address</label>
                                        <div className="input-wrapper">
                                            <MapPin size={18} className="input-icon" />
                                            <input
                                                type="text"
                                                name="location"
                                                placeholder="123 Business St, City"
                                                value={formData.location}
                                                onChange={handleInputChange}
                                                className={errors.location ? 'error' : ''}
                                            />
                                        </div>
                                        {errors.location && <span className="error-text">{errors.location}</span>}
                                    </div>
                                )}

                                {/* Description (Business Only) */}
                                {role === 'business' && (
                                    <div className="input-group" style={{ gridColumn: '1 / -1' }}>
                                        <label>Description (Optional)</label>
                                        <div className="input-wrapper" style={{ alignItems: 'flex-start' }}>
                                            <FileText size={18} className="input-icon" style={{ marginTop: '12px' }} />
                                            <textarea
                                                name="description"
                                                placeholder="Tell us about your business..."
                                                value={formData.description}
                                                onChange={handleInputChange}
                                                style={{ minHeight: '80px', paddingTop: '10px' }}
                                            />
                                        </div>
                                    </div>
                                )}

                                {/* Email & Password */}
                                <div className="input-group" style={{ gridColumn: '1 / -1' }}>
                                    <label>Email Address</label>
                                    <div className="input-wrapper">
                                        <Mail size={18} className="input-icon" />
                                        <input
                                            type="email"
                                            name="email"
                                            placeholder="you@example.com"
                                            value={formData.email}
                                            onChange={handleInputChange}
                                            className={errors.email ? 'error' : ''}
                                        />
                                    </div>
                                    {errors.email && <span className="error-text">{errors.email}</span>}
                                </div>

                                <div className="input-group" style={{ gridColumn: '1 / -1' }}>
                                    <label>Password</label>
                                    <div className="input-wrapper">
                                        <Lock size={18} className="input-icon" />
                                        <input
                                            type={showPassword ? "text" : "password"}
                                            name="password"
                                            placeholder="••••••••"
                                            value={formData.password}
                                            onChange={handleInputChange}
                                            className={errors.password ? 'error' : ''}
                                        />
                                        <button
                                            type="button"
                                            onClick={() => setShowPassword(!showPassword)}
                                            className="password-toggle"
                                        >
                                            {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                                        </button>
                                    </div>
                                    {errors.password && <span className="error-text">{errors.password}</span>}
                                </div>
                            </div>
                        </motion.div>
                    </AnimatePresence>

                    <button
                        className="btn-primary"
                        style={{ width: '100%', padding: '14px', marginTop: '24px' }}
                        disabled={isLoading}
                    >
                        {isLoading ? (
                            <motion.div
                                animate={{ rotate: 360 }}
                                transition={{ duration: 1, repeat: Infinity, ease: "linear" }}
                            >
                                <ArrowRight size={20} />
                            </motion.div>
                        ) : (
                            <>
                                <UserPlus size={20} />
                                Create {role === 'user' ? 'User' : 'Business'} Account
                            </>
                        )}
                    </button>
                </form>

                <div style={{ marginTop: '32px', textAlign: 'center', color: 'var(--text-muted)' }}>
                    Already have an account?{' '}
                    <Link to="/login" style={{ color: 'var(--primary)', textDecoration: 'none', fontWeight: '600' }}>
                        Sign In
                    </Link>
                </div>
            </motion.div>
        </div>
    );
};

export default SignUp;
