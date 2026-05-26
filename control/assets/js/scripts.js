jQuery(document).ready(function($) {

    // Initializations
    updateFloatingLabels();
    $('.country-code-select').trigger('change');

    // --- Backup & Restore System ---

    $(document).on('click', '#control-generate-backup', function() {
        const $btn = $(this);
        const originalText = $btn.text();
        $btn.prop('disabled', true).text('جاري توليد النسخة الاحتياطية...');

        $.post(control_ajax.ajax_url, { action: 'control_create_backup', nonce: control_ajax.nonce }, function(res) {
            if (res.success) {
                const blob = new Blob([res.data.json], { type: 'application/json' });
                const link = document.createElement("a");
                const url = URL.createObjectURL(blob);
                link.setAttribute("href", url);
                link.setAttribute("download", res.data.filename);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                alert('تم إنشاء النسخة الاحتياطية بنجاح.');
            } else {
                alert('فشل إنشاء النسخة الاحتياطية: ' + res.data);
            }
            $btn.prop('disabled', false).text(originalText);
        });
    });

    $(document).on('click', '#add-category-btn', function() {
        $('#category-modal-title').text('إضافة تصنيف');
        $('#matjar-category-form')[0].reset();
        $('#category-id').val('0');
        $('#matjar-category-modal').css('display', 'flex');
    });

    $(document).on('click', '.edit-category', function() {
        const cat = $(this).data('cat');
        $('#category-modal-title').text('تعديل التصنيف');
        $('#category-id').val(cat.id);
        $('#category-name').val(cat.name);
        $('#category-parent-id').val(cat.parent_id);
        $('#matjar-category-modal').css('display', 'flex');
    });

    $(document).on('click', '.matjar-close-cat-modal', function() {
        $('#matjar-category-modal').hide();
    });

    $(document).on('submit', '#matjar-category-form', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('جاري الحفظ...');

        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_category&nonce=' + control_ajax.nonce, function(res) {
            if (res.success) {
                location.reload();
            } else {
                alert('حدث خطأ');
                $btn.prop('disabled', false).text('حفظ');
            }
        });
    });

    $(document).on('click', '.delete-category', function() {
        if (!confirm('هل أنت متأكد من حذف هذا التصنيف؟')) return;
        const id = $(this).data('id');
        $.post(control_ajax.ajax_url, { action: 'control_delete_category', id: id, nonce: control_ajax.nonce }, function() {
            location.reload();
        });
    });

    $(document).on('click', '#control-restore-trigger', function() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.json';
        input.onchange = e => {
            const file = e.target.files[0];
            const reader = new FileReader();
            reader.onload = event => {
                const backupData = event.target.result;
                if (confirm('تحذير هام: سيتم استبدال كافة البيانات الحالية. هل تريد الاستمرار؟')) {
                    $.post(control_ajax.ajax_url, { action: 'control_restore_backup', backup_data: backupData, nonce: control_ajax.nonce }, function(res) {
                        if (res.success) {
                            alert('تمت استعادة النظام بنجاح. سيتم إعادة تحميل الصفحة.');
                            location.reload();
                        } else {
                            alert('خطأ في الاستعادة: ' + res.data);
                        }
                    });
                }
            };
            reader.readAsText(file);
        };
        input.click();
    });

    // --- Auth Toggling & Multi-step Registration ---

    function switchAuthView(hideSelector, showSelector) {
        $(hideSelector).fadeOut(250, function() {
            $(showSelector).fadeIn(250);
        });
    }

    $(document).on('click', '#switch-to-register', function() { switchAuthView('#control-login-container', '#control-register-container'); initRegDots(); setTimeout(updateFloatingLabels, 300); });
    $(document).on('click', '#switch-to-login-from-reg', function() { switchAuthView('#control-register-container', '#control-login-container'); setTimeout(updateFloatingLabels, 300); });
    $(document).on('click', '#switch-to-forgot', function() { switchAuthView('#control-login-container', '#control-forgot-container'); setTimeout(updateFloatingLabels, 300); });
    $(document).on('click', '#switch-to-login-from-forgot', function() { switchAuthView('#control-forgot-container', '#control-login-container'); setTimeout(updateFloatingLabels, 300); });

    let regCurrentStep = 1;
    function getRegTotalSteps() { return $('.reg-step').length; }

    function initRegDots() {
        const total = getRegTotalSteps();
        let dots = '';
        for(let i=1; i<=total; i++) dots += `<span class="step-dot ${i===1?'active':''}" data-step="${i}"></span>`;
        $('#reg-step-indicator').html(dots);
    }

    $(document).on('click', '#reg-next', function() {
        const $current = $(`#reg-step-${regCurrentStep}`);

        if (validateRegStep(regCurrentStep)) {
            // Check if this step has email and needs OTP
            const email = $current.find('.reg-email-input').val();
            if (email && !$current.hasClass('email-verified')) {
                sendOTP(email);
                return;
            }

            $current.hide();
            regCurrentStep++;
            $(`#reg-step-${regCurrentStep}`).fadeIn(300);
            updateRegUI();
        }
    });

    $(document).on('click', '#reg-prev', function() {
        if ($('#reg-step-otp').is(':visible')) {
            $('#reg-step-otp').hide();
            $(`#reg-step-${regCurrentStep}`).fadeIn(300);
        } else {
            $(`#reg-step-${regCurrentStep}`).hide();
            regCurrentStep--;
            $(`#reg-step-${regCurrentStep}`).fadeIn(300);
        }
        updateRegUI();
    });

    function updateRegUI() {
        const total = getRegTotalSteps();
        const isOTP = $('#reg-step-otp').is(':visible');

        $('#reg-prev').toggle(regCurrentStep > 1 || isOTP);
        $('#reg-next').toggle(regCurrentStep < total && !isOTP);
        $('#reg-submit').toggle(regCurrentStep === total && !isOTP);

        $('.step-dot').removeClass('active');
        if (!isOTP) {
            $(`.step-dot[data-step="${regCurrentStep}"]`).addClass('active');
        }
    }

    // OTP Logic
    let otpTimer = 0;
    function sendOTP(email) {
        $('#reg-error').hide();
        $.post(control_ajax.ajax_url, {
            action: 'control_send_otp',
            email: email,
            nonce: control_ajax.nonce
        }, function(res) {
            if (res.success) {
                $(`#reg-step-${regCurrentStep}`).hide();
                $('#reg-step-otp').fadeIn(300);
                updateRegUI();
                startOTPTimer();
                $('.otp-digit').first().focus();
            } else {
                $('#reg-error').html('<span class="dashicons dashicons-warning"></span> ' + res.data.message).addClass('error').fadeIn();
            }
        });
    }

    function startOTPTimer() {
        otpTimer = 60;
        $('#resend-otp-btn').prop('disabled', true).css('opacity', '0.5');
        const interval = setInterval(() => {
            otpTimer--;
            $('#otp-cooldown').text(`(${otpTimer}s)`);
            if (otpTimer <= 0) {
                clearInterval(interval);
                $('#resend-otp-btn').prop('disabled', false).css('opacity', '1');
                $('#otp-cooldown').text('');
            }
        }, 1000);
    }

    $('.otp-digit').on('input', function() {
        const val = $(this).val();
        if (val && $(this).data('index') < 5) {
            $(this).next('.otp-digit').focus();
        }
        checkFullOTP();
    });

    $('.otp-digit').on('keydown', function(e) {
        if (e.key === 'Backspace' && !$(this).val() && $(this).data('index') > 0) {
            $(this).prev('.otp-digit').focus();
        }
    });

    // Real-time Uniqueness Validation
    let uniqueTimers = {};
    $(document).on('blur change', '.reg-email-input, #reg-phone-body, #user-email, #user-phone-body', function() {
        const $el = $(this);
        const value = $el.val();
        if (!value) return;

        let field = 'email';
        let valToCheck = value;

        if ($el.attr('id') === 'reg-phone-body' || $el.attr('id') === 'user-phone-body') {
            field = 'phone';
            const country = $el.closest('.integrated-phone-field').find('.country-code-select').val();
            valToCheck = country + value;
        }

        const excludeId = $('#user-id').val() || 0;

        clearTimeout(uniqueTimers[field]);
        uniqueTimers[field] = setTimeout(() => {
            $.post(control_ajax.ajax_url, {
                action: 'control_check_uniqueness',
                field: field,
                value: valToCheck,
                exclude_id: excludeId,
                nonce: control_ajax.nonce
            }, function(res) {
                const $container = $el.closest('.control-form-group');
                if (!res.success) {
                    $el.css('border-color', '#ef4444');
                    if (!$container.find('.unique-error').length) {
                        $container.append(`<div class="unique-error" style="color:#ef4444; font-size:0.7rem; margin-top:4px; font-weight:700;">${res.data.message}</div>`);
                    }
                } else {
                    $el.css('border-color', '');
                    $container.find('.unique-error').remove();
                }
            });
        }, 300);
    });

    // Helper for floating labels
    function updateFloatingLabels() {
        $('.control-form-group input, .control-form-group select, .control-form-group textarea').each(function() {
            if ($(this).val()) {
                $(this).closest('.control-form-group').addClass('has-value');
            } else {
                $(this).closest('.control-form-group').removeClass('has-value');
            }
        });
    }

    $(document).on('focus blur change input', '.control-form-group input, .control-form-group select, .control-form-group textarea', function() {
        if ($(this).val()) {
            $(this).closest('.control-form-group').addClass('has-value');
        } else {
            $(this).closest('.control-form-group').removeClass('has-value');
        }
    });

    // Country Code Flag Switcher
    $(document).on('change', '.country-code-select', function() {
        const flag = $(this).find(':selected').data('flag');
        $(this).siblings('.selected-flag').text(flag);
    });

    function checkFullOTP() {
        let otp = '';
        $('.otp-digit').each(function() { otp += $(this).val(); });
        if (otp.length === 6) {
            verifyOTP(otp);
        }
    }

    function verifyOTP(otp) {
        const email = $(`.reg-step:visible`).prevAll('.reg-step').find('.reg-email-input').val() || $(`.reg-step`).find('.reg-email-input').val();
        $('#otp-feedback').hide().removeClass('error success');

        $.post(control_ajax.ajax_url, {
            action: 'control_verify_otp',
            email: email,
            otp: otp,
            nonce: control_ajax.nonce
        }, function(res) {
            if (res.success) {
                $('#otp-feedback').html('<span class="dashicons dashicons-yes"></span> ' + res.data).addClass('success').fadeIn();
                $(`#reg-step-${regCurrentStep}`).addClass('email-verified');
                setTimeout(() => {
                    $('#reg-step-otp').hide();
                    regCurrentStep++;
                    $(`#reg-step-${regCurrentStep}`).fadeIn(300);
                    updateRegUI();
                }, 1000);
            } else {
                $('#otp-feedback').html('<span class="dashicons dashicons-warning"></span> ' + res.data.message).addClass('error').fadeIn();
                $('.otp-digit').val('').first().focus();
            }
        });
    }

    $('#resend-otp-btn').on('click', function() {
        const email = $('.reg-email-input').val();
        sendOTP(email);
    });

    function validateRegStep(step) {
        let valid = true;
        const root = document.documentElement;
        const errorColor = '#ef4444';

        $(`#reg-step-${step} [required]`).each(function() {
            const $field = $(this);
            const $container = $field.closest('.control-form-group');
            const val = $field.val();

            if (!val || (val && val.trim() === '')) {
                $container.find('input, select, textarea, .integrated-phone-field').css('border-color', errorColor);
                if (!$container.find('.error-msg').length) {
                    $container.append(`<div class="error-msg" style="color:${errorColor}; font-size:0.7rem; margin-top:4px; font-weight:700;">هذا الحقل مطلوب</div>`);
                }
                valid = false;
            } else {
                $container.find('input, select, textarea, .integrated-phone-field').css('border-color', '');
                $container.find('.error-msg').remove();

                // Specific Validation: Phone
                if ($field.attr('id') === 'reg-phone-body') {
                    if (val.length < 7) {
                        $container.find('.integrated-phone-field').css('border-color', errorColor);
                        if (!$container.find('.error-msg').length) {
                            $container.append(`<div class="error-msg" style="color:${errorColor}; font-size:0.7rem; margin-top:4px; font-weight:700;">رقم الهاتف غير صالح</div>`);
                        }
                        valid = false;
                    }
                }

                // Specific Validation: Password Matching
                if ($field.attr('id') === 'reg-confirm-password') {
                    const pass = $('#reg-password').val();
                    if (val !== pass) {
                        $field.css('border-color', errorColor);
                        if (!$container.find('.error-msg').length) {
                            $container.append(`<div class="error-msg" style="color:${errorColor}; font-size:0.7rem; margin-top:4px; font-weight:700;">كلمة المرور غير متطابقة</div>`);
                        }
                        valid = false;
                    }
                }
            }
        });
        return valid;
    }

    // --- Authentication Actions ---

    $(document).on('submit', '#control-login-form', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const phoneFull = $('#login-country-code').val() + $('#login-phone-body').val();
        $('#login-phone-full').val(phoneFull);

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> جاري التحقق...');
        $('#login-error').hide().removeClass('error success');

        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_login&nonce=' + control_ajax.nonce, function(res) {
            if (res.success) {
                $('#login-error').html('<span class="dashicons dashicons-yes"></span> ' + res.data).addClass('success').fadeIn();
                setTimeout(() => window.location.reload(), 1000);
            } else {
                $btn.prop('disabled', false).text('تسجيل الدخول للنظام');
                $('#login-error').html('<span class="dashicons dashicons-warning"></span> ' + (res.data.message || 'بيانات الدخول غير صحيحة')).addClass('error').fadeIn();
            }
        });
    });

    $(document).on('submit', '#control-register-form', function(e) {
        e.preventDefault();
        const $btn = $('#reg-submit');
        const phoneFull = $('#reg-country-code').val() + $('#reg-phone-body').val();

        if ($('#reg-password').val().length < 8) {
            $('#reg-error').html('<span class="dashicons dashicons-warning"></span> كلمة المرور يجب أن لا تقل عن 8 أحرف').addClass('error').fadeIn();
            return;
        }

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> جاري معالجة طلبك...');
        $('#reg-error').hide().removeClass('error success');

        const formData = $(this).serializeArray();
        formData.push({ name: 'phone', value: phoneFull });
        formData.push({ name: 'action', value: 'control_register' });
        formData.push({ name: 'nonce', value: control_ajax.nonce });

        $.post(control_ajax.ajax_url, formData, function(res) {
            if (res.success) {
                $('#reg-error').html('<span class="dashicons dashicons-yes"></span> ' + 'تم التسجيل بنجاح! جاري تحويلك...').addClass('success').fadeIn();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                $btn.prop('disabled', false).text('إتمام التسجيل');
                $('#reg-error').html('<span class="dashicons dashicons-warning"></span> ' + (res.data.message || 'حدث خطأ أثناء التسجيل')).addClass('error').fadeIn();
            }
        });
    });

    $(document).on('submit', '#control-reset-password-form', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const pass = $('#reset-new-password').val();
        const confirm = $('#reset-confirm-password').val();

        if (pass !== confirm) {
            $('#reset-feedback').html('<span class="dashicons dashicons-warning"></span> كلمة المرور غير متطابقة').addClass('error').fadeIn();
            return;
        }

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> جاري التحديث...');
        $('#reset-feedback').hide().removeClass('error success');

        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_process_password_reset&nonce=' + control_ajax.nonce, function(res) {
            if (res.success) {
                $('#reset-feedback').html('<span class="dashicons dashicons-yes"></span> ' + res.data).addClass('success').fadeIn();
                setTimeout(() => window.location.href = control_ajax.home_url, 2000);
            } else {
                $btn.prop('disabled', false).text('تحديث كلمة المرور');
                $('#reset-feedback').html('<span class="dashicons dashicons-warning"></span> ' + (res.data.message || 'حدث خطأ')).addClass('error').fadeIn();
            }
        });
    });

    let recoveryEmail = '';
    $(document).on('submit', '#control-forgot-form', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const phoneFull = $('#forgot-country-code').val() + $('#forgot-phone-body').val();

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> جاري الإرسال...');
        $('#forgot-feedback').hide().removeClass('error success');

        $.post(control_ajax.ajax_url, {
            action: 'control_forgot_password',
            phone: phoneFull,
            nonce: control_ajax.nonce
        }, function(res) {
            $btn.prop('disabled', false).text('إرسال رمز التحقق');
            if (res.success) {
                recoveryEmail = res.data.email;
                $('#forgot-step-1').hide();
                $('#forgot-step-2').fadeIn();
                $('#forgot-feedback').html('<span class="dashicons dashicons-yes"></span> ' + res.data.message).addClass('success').fadeIn();
                $('.recovery-otp').first().focus();
            } else {
                $('#forgot-feedback').html('<span class="dashicons dashicons-warning"></span> ' + (res.data.message || 'حدث خطأ')).addClass('error').fadeIn();
            }
        });
    });

    $(document).on('click', '#verify-recovery-otp-btn', function() {
        let otp = '';
        $('.recovery-otp').each(function() { otp += $(this).val(); });

        if (otp.length < 6) return;

        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> جاري التحقق...');
        $('#forgot-feedback').hide().removeClass('error success');

        $.post(control_ajax.ajax_url, {
            action: 'control_verify_recovery_otp',
            email: recoveryEmail,
            otp: otp,
            nonce: control_ajax.nonce
        }, function(res) {
            if (res.success) {
                $('#forgot-step-2').hide();
                $('#forgot-step-3').fadeIn();
                $('#forgot-feedback').html('<span class="dashicons dashicons-yes"></span> ' + res.data).addClass('success').fadeIn();
                updateFloatingLabels();
            } else {
                $btn.prop('disabled', false).text('تحقق من الرمز');
                $('#forgot-feedback').html('<span class="dashicons dashicons-warning"></span> ' + res.data.message).addClass('error').fadeIn();
                $('.recovery-otp').val('').first().focus();
            }
        });
    });

    $(document).on('click', '#reset-recovery-pass-btn', function() {
        const pass = $('#recovery-new-password').val();
        const confirm = $('#recovery-confirm-password').val();

        if (pass.length < 8) {
            $('#forgot-feedback').html('<span class="dashicons dashicons-warning"></span> كلمة المرور قصيرة جداً').addClass('error').fadeIn();
            return;
        }

        if (pass !== confirm) {
            $('#forgot-feedback').html('<span class="dashicons dashicons-warning"></span> كلمة المرور غير متطابقة').addClass('error').fadeIn();
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> جاري الحفظ...');

        $.post(control_ajax.ajax_url, {
            action: 'control_reset_password_recovery',
            email: recoveryEmail,
            password: pass,
            nonce: control_ajax.nonce
        }, function(res) {
            if (res.success) {
                $('#forgot-feedback').html('<span class="dashicons dashicons-yes"></span> ' + res.data).addClass('success').fadeIn();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                $btn.prop('disabled', false).text('تحديث كلمة المرور والدخول');
                $('#forgot-feedback').html('<span class="dashicons dashicons-warning"></span> ' + res.data.message).addClass('error').fadeIn();
            }
        });
    });

    $('.recovery-otp').on('input', function() {
        if ($(this).val() && $(this).data('index') < 5) {
            $(this).next('.recovery-otp').focus();
        }
    });

    $('.recovery-otp').on('keydown', function(e) {
        if (e.key === 'Backspace' && !$(this).val() && $(this).data('index') > 0) {
            $(this).prev('.recovery-otp').focus();
        }
    });

    // --- SPA Routing Logic ---
    $(document).on('click', 'a[href*="' + window.location.origin + '"]', function(e) {
        const url = $(this).attr('href');
        // Only intercept if it's not a WP-admin link or a logout link
        if (url.includes('wp-admin') || url.includes('action=logout') || url.includes('wp-login')) return;

        e.preventDefault();
        navigateTo(url);
    });

    function navigateTo(url) {
        showSync('جاري التحميل...');
        history.pushState(null, '', url);

        $.get(url, function(html) {
            const $newDoc = $(html);
            const $newContent = $newDoc.find('.control-content-inner');
            const $currentContent = $('.control-content-inner');

            if ($newContent.length && $currentContent.length) {
                $currentContent.fadeOut(200, function() {
                    $currentContent.html($newContent.html()).fadeIn(200);
                    hideSync();
                    // Re-initialize dynamic elements
                    updateFloatingLabels();
                    updateCartUI();
                    $(window).scrollTop(0);
                });
            } else {
                window.location.href = url;
            }
        }).fail(function() {
            window.location.href = url;
        });
    }

    window.onpopstate = function() {
        navigateTo(window.location.href);
    };

    // --- Settings System & Real-time Design Preview ---

    $(document).on('input change', '#control-design-form input, #control-design-form select, #control-identity-form input, #control-identity-form select', function() {
        const name = $(this).attr('name');
        const val = $(this).val();
        const root = document.documentElement;

        switch(name) {
            case 'design_sidebar_bg': root.style.setProperty('--control-sidebar-bg', val); break;
            case 'design_btn_primary': root.style.setProperty('--control-primary', val); break;
            case 'design_btn_secondary': root.style.setProperty('--control-primary-soft', val); break;
            case 'design_accent': root.style.setProperty('--control-accent', val); break;
            case 'design_text_main': root.style.setProperty('--control-text', val); break;
            case 'design_bg_main': root.style.setProperty('--control-bg', val); break;
            case 'design_font_size': root.style.setProperty('font-size', val + 'px', 'important'); break;
            case 'design_font_weight_bold': root.style.setProperty('--control-font-weight-bold', val); break;
            case 'design_high_contrast':
                if(val === 'yes') $('body').css('filter', 'contrast(1.1) saturate(1.1)');
                else $('body').css('filter', 'none');
                break;

            // Auth Previews
            case 'auth_bg_color': root.style.setProperty('--auth-bg-color', val); break;
            case 'auth_bg_image': root.style.setProperty('--auth-bg-image', `url(${val})`); break;
            case 'auth_container_bg': root.style.setProperty('--auth-container-bg', val); break;
            case 'auth_container_opacity': root.style.setProperty('--auth-container-opacity', val); break;
            case 'auth_border_color': root.style.setProperty('--auth-border-color', val); break;
            case 'auth_border_radius': root.style.setProperty('--auth-border-radius', val + 'px'); break;
            case 'auth_input_border': root.style.setProperty('--auth-input-border', val); break;
            case 'auth_input_bg': root.style.setProperty('--auth-input-bg', val); break;
            case 'auth_input_text': root.style.setProperty('--auth-input-text', val); break;
            case 'auth_label_color': root.style.setProperty('--auth-label-color', val); break;
            case 'auth_input_focus': root.style.setProperty('--auth-input-focus', val); break;
        }
    });

    $(document).on('click', '.control-tab-btn', function() {
        const tab = $(this).data('tab');
        $('.control-tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.control-tab-content').hide();
        $('#' + tab).fadeIn(200);
    });

    $(document).on('submit', '.control-system-settings-form', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> جاري الحفظ...');

        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_settings&nonce=' + control_ajax.nonce, function(res) {
            if (res.success) {
                alert('تم حفظ الإعدادات بنجاح');
                location.reload();
            } else {
                alert('خطأ أثناء الحفظ');
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // --- Other Shared Utilities ---

    const syncLoader = $('#control-sync-loader');
    function showSync(text = 'جارٍ تحميل البيانات...') { syncLoader.find('.loader-text').text(text); syncLoader.fadeIn(200); }
    function hideSync() { syncLoader.find('.loader-text').text('تم التحديث بنجاح'); setTimeout(() => syncLoader.fadeOut(400), 1000); }

    $(document).ajaxStart(function() { showSync(); });
    $(document).ajaxStop(function() { hideSync(); });

    $(document).on('click', '#control-refresh-btn, #control-mobile-refresh-btn', function() {
        showSync('جاري مسح التخزين المؤقت وتحديث ملفات النظام...');

        // Clear Application State
        localStorage.clear();
        sessionStorage.clear();

        // Unregister Service Workers if any
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) {
                    registration.unregister();
                }
            });
        }

        // Hard Reload
        setTimeout(() => {
            window.location.href = window.location.origin + window.location.pathname + '?v=' + new Date().getTime();
        }, 800);
    });


    $(document).on('click', '#control-logout-btn, #control-mobile-logout-btn, #control-header-logout', function(e) {
        e.preventDefault();
        showSync('جاري تسجيل الخروج وتأمين الحساب...');

        // Clear Client Storage immediately
        localStorage.clear();
        sessionStorage.clear();

        // Use native WP logout URL (includes correct nonce)
        const logoutUrl = control_ajax.logout_url;

        // First try server-side logout via AJAX, then redirect regardless
        $.post(control_ajax.ajax_url, { action: 'control_logout', nonce: control_ajax.nonce }).always(function() {
            window.location.href = logoutUrl;
        });

        // Fallback for safety
        setTimeout(() => { window.location.href = logoutUrl; }, 1500);
    });

    $(document).on('click', '.control-upload-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const frame = wp.media({ title: 'اختر صورة', multiple: false }).open();
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            const target = btn.parent().find('input[type="hidden"]');
            if (target.length) {
                target.val(attachment.url);
                const previewImg = btn.closest('.control-form-group').find('img');
                if (previewImg.length) {
                    previewImg.attr('src', attachment.url).show();
                    btn.closest('.control-form-group').find('.dashicons').hide();
                }
            }
        });
    });

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        window.controlInstallPrompt = e;
        $('#control-install-banner').fadeIn(300);
    });

    // --- Audit & UI Extras ---

    $(document).on('click', '#control-export-audit-pdf', function() {
        const element = document.getElementById('control-audit-logs-body').closest('table');
        const opt = {
            margin:       10,
            filename:     'control_audit_log.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).save();
    });


    // Expandable Panels
    $(document).on('click', '.control-collapse-trigger', function() {
        $(this).next('.control-collapse-content').slideToggle(200);
    });

    // Notifications Sidebar Toggles
    $(document).on('click', '.tpl-nav-btn', function() {
        $('.tpl-nav-btn').removeClass('active');
        $(this).addClass('active');
        const tpl = $(this).data('tpl');
        $('.tpl-content-section').hide();
        $(`#tpl-section-${tpl}`).fadeIn();
    });

    // Audit Log Actions
    $(document).on('click', '.undo-action', function() {
        const id = $(this).data('id');
        $.post(control_ajax.ajax_url, { action: 'control_undo_activity', log_id: id, nonce: control_ajax.nonce }, (res) => {
            if(res.success) location.reload();
            else alert(res.data);
        });
    });

    $(document).on('click', '.delete-log-entry', function() {
        if(!confirm('هل أنت متأكد من حذف هذا السجل؟')) return;
        const id = $(this).data('id');
        $.post(control_ajax.ajax_url, { action: 'control_delete_activity', log_id: id, nonce: control_ajax.nonce }, () => location.reload());
    });

    $(document).on('click', '.view-log-info', function() {
        const log = $(this).data('log');
        let details = `
            <div style="text-align:right; direction:rtl; font-family:Rubik, sans-serif;">
                <p><strong>المسؤول:</strong> ${log.user_id}</p>
                <p><strong>العملية:</strong> ${log.action_type}</p>
                <p><strong>الوصف:</strong> ${log.description}</p>
                <p><strong>الجهاز:</strong> ${log.device_type}</p>
                <p><strong>المتصفح:</strong> ${log.device_info}</p>
                <p><strong>IP:</strong> ${log.ip_address}</p>
                <p><strong>التاريخ:</strong> ${log.action_date}</p>
                <p><strong>البيانات الوصفية:</strong></p>
                <pre style="background:#f1f5f9; padding:10px; border-radius:8px; font-size:0.7rem; overflow-x:auto;">${JSON.stringify(JSON.parse(log.meta_data || '{}'), null, 2)}</pre>
            </div>
        `;

        // Simple overlay for log details
        const overlay = $('<div style="position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:100000; display:flex; align-items:center; justify-content:center; backdrop-filter:blur(4px);"></div>');
        const modal = $('<div class="control-card" style="width:90%; max-width:600px; padding:30px;"><h3>تفاصيل السجل</h3>' + details + '<button class="control-btn" style="width:100%; margin-top:20px;">إغلاق</button></div>');

        overlay.append(modal);
        $('body').append(overlay);

        modal.find('button').on('click', () => overlay.remove());
        overlay.on('click', (e) => { if(e.target === overlay[0]) overlay.remove(); });
    });

    // Self Profile Actions
    let selfCurrentStep = 1;
    function showSelfStep(step) {
        $('.self-wizard-step').hide();
        $(`#self-step-${step}`).fadeIn(300);
        $('#self-wizard-dots .dot').removeClass('active');
        $(`#self-wizard-dots .dot[data-step="${step}"]`).addClass('active');
        $('#self-wizard-prev').toggle(step > 1);
        $('#self-wizard-next').toggle(step < 4);
        $('#self-wizard-submit').toggle(step === 4);
        const labels = { 1: 'المعلومات الشخصية', 2: 'المؤهلات الأكاديمية', 3: 'المعلومات المهنية', 4: 'إعدادات الحساب' };
        $('#self-wizard-step-label').text(labels[step]);
        selfCurrentStep = step;
    }

    $('#self-wizard-next').on('click', function() { showSelfStep(selfCurrentStep + 1); });
    $('#self-wizard-prev').on('click', function() { showSelfStep(selfCurrentStep - 1); });

    $('#control-edit-profile-btn').on('click', function() {
        showSelfStep(1);
        $('#self-profile-modal').css('display', 'flex');
    });

    $('.close-self-modal').on('click', function() {
        $('#self-profile-modal').hide();
    });

    $('#upload-self-image, #self-profile-preview').on('click', function(e) {
        e.preventDefault();
        const frame = wp.media({ title: 'اختر صورة شخصية', multiple: false }).open();
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#self-image-url').val(attachment.url);
            $('#self-profile-preview').html(`<img src="${attachment.url}" style="width:100%; height:100%; object-fit:cover;">`);
        });
    });

    $('#self-profile-form').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('جاري الحفظ...');

        const formData = $(this).serialize() + '&action=control_update_profile&nonce=' + control_ajax.nonce;
        $.post(control_ajax.ajax_url, formData, function(res) {
            if (res.success) {
                alert(res.data);
                location.reload();
            } else {
                alert(res.data || 'حدث خطأ');
                $btn.prop('disabled', false).text('حفظ التعديلات');
            }
        });
    });

    // Auth Control Panel Logic
    $('#auth-control-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $btn = $form.find('button[type="submit"]');
        const originalText = $btn.text();

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> جاري الحفظ...');

        // Manual collection of checkboxes to handle '0' values for unchecked states
        let checkboxes = {};
        $form.find('input[type="checkbox"]:not(.field-enabled):not(.field-required)').each(function() {
            if (this.name) {
                checkboxes[this.name] = this.checked ? '1' : '0';
            }
        });

        // Collect registration fields configuration
        let fields = [];
        $('#reg-fields-config-body tr').each(function() {
            fields.push({
                id: $(this).find('.field-id').val(),
                label: $(this).find('.field-label').val(),
                step: parseInt($(this).find('.field-step').val()),
                width: $(this).find('.field-width').val() || 'full',
                enabled: $(this).find('.field-enabled').is(':checked'),
                required: $(this).find('.field-required').is(':checked')
            });
        });

        let formData = $form.serializeArray();

        // Remove existing items that we are going to manually add or that shouldn't be there
        formData = formData.filter(item => !checkboxes.hasOwnProperty(item.name) && item.name !== 'auth_registration_fields');

        // Append manual checkbox values
        Object.keys(checkboxes).forEach(name => {
            formData.push({ name: name, value: checkboxes[name] });
        });

        formData.push({ name: 'action', value: 'control_save_settings' });
        formData.push({ name: 'nonce', value: control_ajax.nonce });
        formData.push({ name: 'auth_registration_fields', value: JSON.stringify(fields) });

        $.post(control_ajax.ajax_url, formData, function(res) {
            if (res.success) {
                $btn.html('<span class="dashicons dashicons-yes"></span> تم الحفظ بنجاح');
                setTimeout(() => location.reload(), 1000);
            } else {
                alert(res.data.message || 'حدث خطأ أثناء الحفظ');
                $btn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Drag and drop for registration fields (simplified)
    if ($.fn.sortable) {
        $('#reg-fields-config-body').sortable({
            handle: 'td:first-child',
            update: function() {
                $('#reg-fields-config-body tr').each(function(index) {
                    $(this).find('.field-order-badge').text(index + 1);
                });
            }
        });
    }

    // --- Advanced Backup & Maintenance Tools ---

    let currentDestructiveAction = null;

    $('#export-user-package-btn').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).text('جاري التجهيز...');

        $.post(control_ajax.ajax_url, { action: 'control_export_user_package', nonce: control_ajax.nonce }, function(res) {
            if (res.success) {
                const blob = new Blob([res.data.json], { type: 'application/json' });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = res.data.filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } else {
                alert(res.data);
            }
            $btn.prop('disabled', false).text('تصدير الحزمة الآن');
        });
    });

    $('#bulk-delete-all-btn').on('click', function() {
        currentDestructiveAction = 'bulk_delete_all_users';
        $('#destructive-modal-title').text('حذف كافة الحسابات');
        $('#destructive-modal-desc').text('أنت على وشك حذف كافة الكوادر البشرية المسجلة. لن يتم حذف حسابك الحالي. هذا الإجراء نهائي ولا يمكن التراجع عنه.');
        $('#reset-word-verification').hide();
        $('#control-destructive-modal').css('display', 'flex');
    });

    $('#system-reset-btn').on('click', function() {
        currentDestructiveAction = 'system_data_reset';
        $('#destructive-modal-title').text('تصفير النظام بالكامل');
        $('#destructive-modal-desc').text('سيتم مسح كافة الكوادر، سجلات النشاط، والبيانات المدخلة. سيتم الحفاظ على إعدادات النظام، الأدوار، والقوالب فقط لضمان بقاء الهيكل الأساسي.');
        $('#reset-word-verification').show();
        $('#destructive-verify-word').val('');
        $('#control-destructive-modal').css('display', 'flex');
    });

    $('#confirm-destructive-btn').on('click', function() {
        if (currentDestructiveAction === 'system_data_reset') {
            if ($('#destructive-verify-word').val() !== 'تأكيد') {
                alert('يرجى كتابة كلمة "تأكيد" بشكل صحيح للمتابعة.');
                return;
            }
        }

        const $btn = $(this);
        $btn.prop('disabled', true).text('جاري التنفيذ...');

        $.post(control_ajax.ajax_url, { action: 'control_' + currentDestructiveAction, nonce: control_ajax.nonce }, function(res) {
            if (res.success) {
                alert(res.data);
                location.reload();
            } else {
                alert(res.data);
                $btn.prop('disabled', false).text('نعم، تنفيذ الآن');
            }
        });
    });

    // --- Policies & Terms Management ---

    $(document).on('click', '#add-new-policy-btn', function() {
        $('#policy-modal-title').text('إضافة سياسة جديدة');
        $('#control-policy-form')[0].reset();
        $('#policy-id').val('0');
        $('#control-policy-modal').css('display', 'flex');
    });

    $(document).on('click', '.edit-policy-btn', function() {
        const policy = $(this).data('policy');
        $('#policy-modal-title').text('تحرير السياسة: ' + policy.title);
        $('#policy-id').val(policy.id);
        $('#policy-title').val(policy.title);
        $('#policy-content').val(policy.content);
        $('#control-policy-modal').css('display', 'flex');
    });

    $(document).on('click', '.delete-policy-btn', function() {
        if (!confirm('هل أنت متأكد من حذف هذه السياسة؟ لا يمكن التراجع عن هذا الإجراء.')) return;
        const id = $(this).data('id');
        $.post(control_ajax.ajax_url, { action: 'control_delete_policy', id: id, nonce: control_ajax.nonce }, function(res) {
            if (res.success) location.reload();
            else alert(res.data);
        });
    });

    $('#control-policy-form').on('submit', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('جاري الحفظ...');

        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_policy&nonce=' + control_ajax.nonce, function(res) {
            if (res.success) {
                alert('تم حفظ السياسة بنجاح');
                location.reload();
            } else {
                alert(res.data || 'حدث خطأ أثناء الحفظ');
                $btn.prop('disabled', false).text('حفظ السياسة');
            }
        });
    });

    // --- Email System Logic ---

    let emailTargetIds = [];
    let emailTemplates = [];

    $(document).on('click', '#control-send-email-blast-btn', function() {
        const selected = $('.user-bulk-select:checked').map((_, el) => el.value).get();
        if (selected.length === 0) {
            alert('يرجى اختيار مستخدم واحد على الأقل من القائمة أولاً.');
            return;
        }
        openEmailComposer(selected);
    });

    function openEmailComposer(ids) {
        emailTargetIds = ids;
        const count = ids.length;
        const $modal = $('#control-email-composer-modal');
        const $templateSelect = $('#email-template-select');

        $('#email-target-display').text(count === 1 ? 'إرسال بريد لمستخدم واحد' : `إرسال بريد لـ ${count} مستخدم مختار`);

        // Fetch templates
        $.post(control_ajax.ajax_url, { action: 'control_get_email_templates', nonce: control_ajax.nonce }, function(res) {
            if (res.success) {
                emailTemplates = res.data;
                $templateSelect.find('option:not([value="custom"])').remove();
                res.data.forEach(tpl => {
                    $templateSelect.append(`<option value="${tpl.template_key}">${tpl.subject}</option>`);
                });
            }
        });

        $('#email-preview-container').hide();
        const $form = $('#control-email-composer-form');
        if ($form.length) $form[0].reset();
        $modal.css('display', 'flex');
    }

    $(document).on('change', '#email-template-select', function() {
        const key = $(this).val();
        if (key === 'custom') {
            $('#email-subject').val('');
            $('#email-content').val('');
        } else {
            const tpl = emailTemplates.find(t => t.template_key === key);
            if (tpl) {
                $('#email-subject').val(tpl.subject);
                $('#email-content').val(tpl.content);
                updateEmailPreview();
            }
        }
    });

    $(document).on('click', '#preview-email-btn', function() {
        updateEmailPreview();
    });

    function updateEmailPreview() {
        const content = $('#email-content').val();
        if (!content) return;

        $('#email-preview-container').show();
        $('#email-preview-frame').html('<p style="text-align:center;">جاري توليد المعاينة...</p>');

        $.post(control_ajax.ajax_url, {
            action: 'control_preview_email',
            content: content,
            nonce: control_ajax.nonce
        }, function(res) {
            if (res.success) {
                $('#email-preview-frame').html(res.data);
            }
        });
    }

    $(document).on('submit', '#control-email-composer-form', function(e) {
        e.preventDefault();
        if (!confirm('هل أنت متأكد من رغبتك في إرسال هذا البريد الآن؟')) return;

        const $btn = $('#send-email-final-btn');
        const originalText = $btn.text();
        $btn.prop('disabled', true).text('جاري الإرسال...');

        $.post(control_ajax.ajax_url, {
            action: 'control_send_manual_email',
            user_ids: emailTargetIds,
            subject: $('#email-subject').val(),
            content: $('#email-content').val(),
            nonce: control_ajax.nonce
        }, function(res) {
            if (res.success) {
                alert(res.data);
                $('#control-email-composer-modal').hide();
                $('.user-bulk-select').prop('checked', false);
                $('.user-card-item').removeClass('selected');
                $('#bulk-actions-toolbar').hide();
                $('#select-all-users').prop('checked', false);
            } else {
                alert(res.data.message || 'حدث خطأ أثناء الإرسال');
            }
            $btn.prop('disabled', false).text(originalText);
        });
    });

    // --- Matjar Cart Logic ---
    let matjarCart = JSON.parse(localStorage.getItem('matjar_cart')) || [];

    function updateCartUI() {
        const count = matjarCart.length;
        const $cartBadge = $('#matjar-cart-count');

        if (count > 0) {
            $cartBadge.text(count).css('display', 'flex');
        } else {
            $cartBadge.hide();
        }

        const $cartContainer = $('#matjar-cart-items');
        if (!$cartContainer.length) return;

        if (count === 0) {
            $cartContainer.html('<p style="text-align: center; padding: 40px; color: var(--control-muted);">السلة فارغة حالياً</p>');
            $('#cart-total').text('0 EGP');
            $('#confirm-order-btn').prop('disabled', true);
            return;
        }

        let html = '<div class="cart-items-list" style="display:flex; flex-direction:column; gap:15px;">';
        let subtotal = 0;
        matjarCart.forEach((item, index) => {
            const price = parseFloat(item.price);
            subtotal += price;
            html += `
                <div class="cart-item" style="display:flex; justify-content:space-between; align-items:center; background:var(--control-bg); padding:15px; border-radius:12px;">
                    <div style="display:flex; gap:15px; align-items:center;">
                        <img src="${item.img}" style="width:50px; height:50px; border-radius:8px; object-fit:cover;">
                        <div>
                            <div style="font-weight:700;">${item.name}</div>
                            <div style="font-size:0.8rem; color:var(--control-accent); font-weight:800;">${item.price} EGP</div>
                        </div>
                    </div>
                    <button class="remove-from-cart" data-index="${index}" style="background:none; border:none; color:#ef4444; cursor:pointer;">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            `;
        });
        html += '</div>';
        $cartContainer.html(html);

        const shipping = 50.00;
        const total = subtotal + shipping;

        $('#cart-subtotal').text(subtotal.toFixed(2) + ' EGP');
        $('#cart-total').text(total.toFixed(2) + ' EGP');
        $('#cart-next-to-shipping').prop('disabled', false);
    }

    $(document).on('click', '#cart-next-to-shipping', function() {
        $('#checkout-step-1').hide();
        $('#checkout-step-2').fadeIn(200);
    });

    $(document).on('click', '#cart-next-to-confirm', function() {
        const address = $('#checkout-address').val();
        if (!address) {
            alert('يرجى إدخال عنوان التوصيل');
            return;
        }
        $('#confirm-address-display').text(address);
        $('#checkout-step-2').hide();
        $('#checkout-step-3').fadeIn(200);
    });

    $(document).on('click', '.checkout-prev', function() {
        const $current = $(this).closest('.checkout-step');
        $current.hide();
        $current.prev('.checkout-step').fadeIn(200);
    });

    $(document).on('click', '.add-to-cart-btn', function() {
        const $card = $(this).closest('.matjar-product-card');
        const product = {
            id: $(this).data('id'),
            name: $card.find('h4').text(),
            price: parseFloat($card.find('.price-tag').text()),
            img: $card.find('img').attr('src')
        };
        matjarCart.push(product);
        localStorage.setItem('matjar_cart', JSON.stringify(matjarCart));
        updateCartUI();

        // Instant feedback
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.html('<span class="dashicons dashicons-yes"></span> تم الإضافة').css('background', '#059669');
        setTimeout(() => $btn.html(originalText).css('background', ''), 2000);
    });

    $(document).on('click', '.remove-from-cart', function() {
        const index = $(this).data('index');
        matjarCart.splice(index, 1);
        localStorage.setItem('matjar_cart', JSON.stringify(matjarCart));
        updateCartUI();
    });

    $(document).on('click', '#confirm-order-btn', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).text('جاري المعالجة...');

        const shippingAddress = $('#checkout-address').val();
        const orderNotes = $('#checkout-notes').val();

        $.post(control_ajax.ajax_url, {
            action: 'control_place_order',
            cart: matjarCart,
            shipping_address: shippingAddress,
            order_notes: orderNotes,
            nonce: control_ajax.nonce
        }, function(res) {
            if (res.success) {
                alert(res.data);
                matjarCart = [];
                localStorage.removeItem('matjar_cart');
                updateCartUI();
                window.location.href = $('.matjar-floating-nav a').eq(2).attr('href');
            } else {
                alert(res.data.message || 'حدث خطأ');
                $btn.prop('disabled', false).text('إتمام الطلب');
            }
        });
    });

    updateCartUI();

    // --- Matjar Search Logic ---
    $(document).on('input', '#matjar-search', function() {
        const query = $(this).val().toLowerCase();
        $('.matjar-product-card').each(function() {
            const name = $(this).find('h4').text().toLowerCase();
            if (name.includes(query)) {
                $(this).fadeIn(200);
            } else {
                $(this).fadeOut(200);
            }
        });
    });

    // --- Matjar Profile Form ---
    $(document).on('submit', '#matjar-profile-form', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        const originalText = $btn.text();
        $btn.prop('disabled', true).text('جاري الحفظ...');

        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_update_profile&nonce=' + control_ajax.nonce, function(res) {
            if (res.success) {
                alert(res.data || 'تم تحديث الملف الشخصي بنجاح');
            } else {
                alert(res.data.message || 'حدث خطأ أثناء الحفظ');
            }
            $btn.prop('disabled', false).text(originalText);
        });
    });

    // --- Matjar Product Management (Admin) ---
    $(document).on('click', '.add-new-product-btn', function(e) {
        e.preventDefault();
        $('#product-modal-title').text('إضافة منتج جديد');
        $('#matjar-admin-product-form')[0].reset();
        $('#product-id').val('0');
        $('#matjar-product-modal').css('display', 'flex');
    });

    $(document).on('click', '.edit-product', function(e) {
        e.preventDefault();
        const product = $(this).closest('tr').data('product');
        $('#product-modal-title').text('تعديل منتج');
        $('#product-id').val(product.id);
        $('#product-name').val(product.name);
        $('#product-description').val(product.description);
        $('#product-price').val(product.price);
        $('#product-stock').val(product.stock);
        $('#product-image-url').val(product.image_url);
        $('#product-category-id').val(product.category_id);
        $('#matjar-product-modal').css('display', 'flex');
    });

    $(document).on('click', '.matjar-close-modal', function() {
        $('#matjar-product-modal').hide();
    });

    $(document).on('submit', '#matjar-admin-product-form', function(e) {
        e.preventDefault();
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).text('جاري الحفظ...');

        $.post(control_ajax.ajax_url, $(this).serialize() + '&action=control_save_product&nonce=' + control_ajax.nonce, function(res) {
            if (res.success) {
                location.reload();
            } else {
                alert('حدث خطأ');
                $btn.prop('disabled', false).text('حفظ المنتج');
            }
        });
    });

    $(document).on('click', '.delete-product', function(e) {
        e.preventDefault();
        if (!confirm('هل أنت متأكد من الحذف؟')) return;
        const id = $(this).data('id');
        $.post(control_ajax.ajax_url, { action: 'control_delete_product', id: id, nonce: control_ajax.nonce }, function() {
            location.reload();
        });
    });

    $(document).on('click', '#matjar-export-products', function() {
        $.post(control_ajax.ajax_url, { action: 'control_export_products', nonce: control_ajax.nonce }, function(res) {
            if (res.success) {
                const blob = new Blob([res.data.content], { type: 'text/csv' });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = res.data.filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
    });

    $(document).on('click', '#matjar-import-trigger', function() {
        $('#matjar-import-file').click();
    });

    $(document).on('change', '#matjar-import-file', function(e) {
        const file = e.target.files[0];
        const reader = new FileReader();
        reader.onload = function(event) {
            const data = event.target.result;
            $.post(control_ajax.ajax_url, { action: 'control_import_products', data: data, nonce: control_ajax.nonce }, function(res) {
                alert(res.data);
                location.reload();
            });
        };
        reader.readAsText(file);
    });

    $(document).on('click', '.matjar-upload-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const frame = wp.media({ title: 'اختر صورة المنتج', multiple: false }).open();
        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#product-image-url').val(attachment.url);
        });
    });
});
