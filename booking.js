/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM - BOOKING JS
 * Xử lý động chọn dịch vụ, tính giờ kết thúc tự động và hiển thị khung giờ khả dụng
 */

document.addEventListener('DOMContentLoaded', function () {
    const serviceSelect = document.getElementById('booking_service_id');
    const staffSelect   = document.getElementById('booking_staff_id');
    const dateInput     = document.getElementById('booking_date');
    const startTimeInput= document.getElementById('booking_start_time');
    const slotsContainer= document.getElementById('available_slots_container');
    const priceDisplay  = document.getElementById('display_service_price');
    const durationDisplay = document.getElementById('display_service_duration');
    const endTimeNotice = document.getElementById('display_end_time_notice');

    if (!serviceSelect || !dateInput) return;

    // Thiết lập ngày tối thiểu là ngày hôm nay
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
    if (!dateInput.value) {
        dateInput.value = today;
    }

    function updateServiceInfo() {
        const selectedOpt = serviceSelect.options[serviceSelect.selectedIndex];
        if (selectedOpt && selectedOpt.value) {
            const price = selectedOpt.getAttribute('data-price');
            const duration = selectedOpt.getAttribute('data-duration');

            if (priceDisplay && price) {
                priceDisplay.textContent = new Intl.NumberFormat('vi-VN').format(price) + ' đ';
            }
            if (durationDisplay && duration) {
                durationDisplay.textContent = duration + ' phút';
            }
        }
        loadAvailableSlots();
    }

    function calculateEndTime(startTime, durationMinutes) {
        const [hours, minutes] = startTime.split(':').map(Number);
        const totalMinutes = hours * 60 + minutes + Number(durationMinutes);
        const endHours = Math.floor(totalMinutes / 60);
        const endMins  = totalMinutes % 60;
        return `${String(endHours).padStart(2, '0')}:${String(endMins).padStart(2, '0')}`;
    }

    function loadAvailableSlots() {
        const serviceId = serviceSelect.value;
        const staffId   = staffSelect ? staffSelect.value : 0;
        const date      = dateInput.value;

        if (!serviceId || !date) {
            if (slotsContainer) {
                slotsContainer.innerHTML = '<div class="text-muted p-3 text-center border rounded-3 bg-light">Vui lòng chọn dịch vụ và ngày hẹn để xem khung giờ trống.</div>';
            }
            return;
        }

        if (slotsContainer) {
            slotsContainer.innerHTML = '<div class="text-center p-3 text-muted"><span class="spinner-border spinner-border-sm me-2 text-danger"></span> Đang kiểm tra lịch trống của kỹ thuật viên...</div>';
        }

        const apiUrl = window.BASE_URL + '/api/available-slots?service_id=' + encodeURIComponent(serviceId) 
                     + '&staff_id=' + encodeURIComponent(staffId) 
                     + '&date=' + encodeURIComponent(date);

        fetch(apiUrl)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.slots) {
                    renderSlots(data.slots, data.duration);
                } else {
                    slotsContainer.innerHTML = '<div class="alert alert-warning py-2 mb-0">' + (data.message || 'Không tìm thấy khung giờ phù hợp.') + '</div>';
                }
            })
            .catch(err => {
                slotsContainer.innerHTML = '<div class="alert alert-danger py-2 mb-0">Không thể tải khung giờ. Vui lòng kiểm tra lại kết nối.</div>';
            });
    }

    function renderSlots(slots, duration) {
        if (!slotsContainer) return;

        if (slots.length === 0) {
            slotsContainer.innerHTML = '<div class="text-muted p-3 text-center border rounded-3 bg-light">Không có khung giờ nào khả dụng trong ngày đã chọn.</div>';
            return;
        }

        let html = '<div class="row g-2">';
        slots.forEach(slot => {
            const isAvail = slot.is_available;
            const startVal = slot.start_time;
            const endVal = slot.end_time;
            const isSelected = (startTimeInput.value && startTimeInput.value.startsWith(startVal));

            html += `
                <div class="col-4 col-sm-3 col-md-2">
                    <button type="button" 
                            class="slot-badge-btn w-100 ${isAvail ? '' : 'disabled'} ${isSelected ? 'selected' : ''}" 
                            data-start="${startVal}" 
                            data-end="${endVal}"
                            ${isAvail ? '' : 'disabled'}>
                        ${startVal}
                    </button>
                </div>
            `;
        });
        html += '</div>';

        slotsContainer.innerHTML = html;

        // Gán sự kiện click cho các ô giờ
        const slotButtons = slotsContainer.querySelectorAll('.slot-badge-btn:not(.disabled)');
        slotButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                slotButtons.forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                const start = this.getAttribute('data-start');
                const end = this.getAttribute('data-end');

                startTimeInput.value = start;

                if (endTimeNotice) {
                    endTimeNotice.innerHTML = `
                        <div class="alert alert-info py-2 px-3 mt-2 mb-0 d-flex align-items-center">
                            <i class="bi bi-clock-history me-2 fs-5"></i>
                            <div>
                                Giờ bắt đầu: <strong>${start}</strong> — Dự kiến kết thúc lúc: <strong>${end}</strong> (${duration} phút)
                            </div>
                        </div>
                    `;
                }
            });
        });
    }

    serviceSelect.addEventListener('change', updateServiceInfo);
    if (staffSelect) {
        staffSelect.addEventListener('change', loadAvailableSlots);
    }
    dateInput.addEventListener('change', loadAvailableSlots);

    // Khởi chạy ban đầu
    updateServiceInfo();
});
