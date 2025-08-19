# AI Resume Builder - FastAPI Backend

## 🚀 Overview

The AI Resume Builder now features a complete **FastAPI backend service** that provides:

- **🤖 AI-Powered Resume Generation**: Custom resumes tailored to specific job descriptions
- **🔍 LinkedIn Job Scraping**: Automated job discovery and analysis
- **📊 Real-time Task Tracking**: Monitor background processes with live updates
- **🔗 Frontend Integration**: Seamless integration with PHP frontend
- **🐳 Docker Support**: Complete containerized deployment

## 🏗️ Architecture

```
┌─────────────────┐    HTTP/REST API    ┌──────────────────┐
│  PHP Frontend   │ ◄──────────────────► │  FastAPI Backend │
│  (Port 8000)    │                     │  (Port 8001)     │
└─────────────────┘                     └──────────────────┘
         │                                        │
         ▼                                        ▼
┌─────────────────────────────────────────────────────────┐
│              Shared SQLite Database                     │
│              (Persistent Docker Volume)                 │
└─────────────────────────────────────────────────────────┘
```

## 🛠️ Quick Start

### 1. Environment Setup
```bash
# Copy environment template
cp resume_builder_backend/.env.example resume_builder_backend/.env

# Edit .env with your OpenAI API key
nano resume_builder_backend/.env
```

### 2. Docker Deployment (Recommended)
```bash
# Start all services
./docker/docker-manage.sh start

# Access points:
# Frontend: http://localhost:8000
# Backend API: http://localhost:8001
# API Docs: http://localhost:8001/docs
```

### 3. Local Development
```bash
# Install backend dependencies
cd resume_builder_backend
pip install -r requirements.txt

# Start FastAPI server
python api_server.py

# Start PHP frontend (separate terminal)
php -S localhost:8000
```

## 📡 API Endpoints

### Core Services

#### Health & Status
```http
GET  /api/health              # Service health check
GET  /api/config              # API configuration
GET  /api/stats               # Usage statistics
```

#### Job Management
```http
POST /api/jobs/search         # Start job search task
GET  /api/jobs                # List all jobs
GET  /api/jobs/{job_id}       # Get specific job
PUT  /api/jobs/{job_id}/apply # Mark job as applied
DELETE /api/jobs/{job_id}     # Delete job
```

#### Resume Generation
```http
POST /api/resumes             # Generate resume for job
GET  /api/resumes             # List all resumes
GET  /api/resumes/{id}        # Get specific resume
DELETE /api/resumes/{id}      # Delete resume
```

#### Background Management
```http
GET  /api/background          # Get user background
POST /api/background          # Update background text
POST /api/background/upload   # Upload background file
POST /api/background/parse    # AI parse background
```

#### Task Tracking
```http
GET  /api/tasks               # List all tasks
GET  /api/tasks/{task_id}     # Get task status
DELETE /api/tasks/{task_id}   # Delete completed task
```

### Example API Usage

#### Search for Jobs
```bash
curl -X POST "http://localhost:8001/api/jobs/search" \
     -H "Content-Type: application/json" \
     -d '{
       "search_terms": ["AI Engineer", "Machine Learning"],
       "location": "San Francisco, CA",
       "experience_level": "mid_level",
       "max_results": 25
     }'
```

#### Generate Resume
```bash
curl -X POST "http://localhost:8001/api/resumes" \
     -H "Content-Type: application/json" \
     -d '{
       "job_id": 123,
       "background_content": "Your professional background..."
     }'
```

## 🎯 Frontend Integration

### AI Backend Dashboard

Access the comprehensive dashboard at: **http://localhost:8000/backend_dashboard.php**

**Features:**
- ✅ **Real-time Health Monitoring**: Backend service status
- 🔍 **Job Search Interface**: Start automated job searches
- 🤖 **Resume Generation**: Create targeted resumes
- 📝 **Background Management**: Update your professional profile
- 📊 **Task Tracking**: Monitor background processes with progress bars
- 🔄 **Auto-refresh**: Live updates every 3 seconds

### Integration Points

The frontend now includes:

1. **Navigation Integration**: "AI Backend" menu item
2. **Task Management**: Real-time progress tracking
3. **Job Discovery**: Automated LinkedIn job scraping
4. **Resume Automation**: AI-powered resume generation
5. **Status Monitoring**: Health checks and error handling

## 🔧 Configuration

### Environment Variables

```bash
# Required
OPENAI_API_KEY=your_openai_api_key

# Optional
API_HOST=0.0.0.0
API_PORT=8001
DATABASE_PATH=/custom/path/resume.sqlite
CORS_ORIGINS=http://localhost:8000
```

### API Configuration

The service automatically detects:
- **Docker Environment**: Uses mounted volumes and environment variables
- **Local Development**: Uses local config files and relative paths
- **Database Location**: Shared between frontend and backend

## 🐳 Docker Configuration

### Services Overview

| Service | Purpose | Port | Status |
|---------|---------|------|--------|
| `frontend` | PHP Web Interface | 8000 | ✅ Ready |
| `backend` | FastAPI API Service | 8001 | ✅ Ready |
| `database` | SQLite with Persistence | - | ✅ Ready |
| `backup` | Automated Backups | - | ✅ Ready |

### Data Persistence

- **Database Volume**: `db_data` ensures data survives container restarts
- **Automatic Backup**: Hourly backups with 10-backup retention
- **Manual Backup**: `./docker/docker-manage.sh backup-docker`
- **Easy Restore**: `./docker/docker-manage.sh restore-docker backup.sqlite`

## 🔄 Background Task System

### Task Types

1. **Job Search** (`job_search`)
   - LinkedIn scraping and analysis
   - AI-powered search query generation
   - Automatic job deduplication

2. **Resume Generation** (`resume_generation`)
   - Job requirement analysis
   - Background parsing with AI
   - Targeted resume section creation

### Task Tracking

```javascript
// Frontend JavaScript can track tasks
async function trackTask(taskId) {
    const response = await fetch(`/api/tasks/${taskId}`);
    const task = await response.json();
    
    console.log(`Status: ${task.status}`);
    console.log(`Progress: ${task.progress}%`);
}
```

## 🛡️ Error Handling

### API Error Responses

```json
{
  "success": false,
  "message": "Job not found",
  "error": "Job ID 999 does not exist",
  "code": 404
}
```

### Frontend Error Handling

The dashboard includes:
- **Connection Monitoring**: Auto-detects backend availability
- **Graceful Degradation**: Works when backend is offline
- **User Feedback**: Clear error messages and retry options
- **Automatic Recovery**: Reconnects when services restore

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` in config
- [ ] Configure proper CORS origins
- [ ] Set up external backup strategy
- [ ] Configure reverse proxy (nginx)
- [ ] Set resource limits in Docker
- [ ] Configure monitoring and logging

### Scaling Options

- **Horizontal**: Multiple backend instances behind load balancer
- **Vertical**: Increase container resource limits
- **Database**: Move to PostgreSQL for multi-user support
- **Queue**: Add Redis for advanced task management

## 🐛 Troubleshooting

### Common Issues

**Backend Won't Start**
```bash
# Check logs
./docker/docker-manage.sh logs backend

# Verify environment
curl http://localhost:8001/api/health
```

**Database Connection Issues**
```bash
# Check volume
docker volume inspect resume_builder_db_data

# Verify permissions
./docker/docker-manage.sh status
```

**Frontend Can't Connect**
```bash
# Test API directly
curl http://localhost:8001/api/health

# Check CORS settings
curl -H "Origin: http://localhost:8000" http://localhost:8001/api/health
```

## 📚 Development

### Adding New Endpoints

1. **Define endpoint** in `api_endpoints.py`
2. **Add background task** if needed
3. **Update frontend** dashboard
4. **Test integration** with frontend

### API Documentation

- **Swagger UI**: http://localhost:8001/docs
- **ReDoc**: http://localhost:8001/redoc
- **OpenAPI Spec**: http://localhost:8001/openapi.json

## 🤝 Integration Benefits

This FastAPI redesign provides:

✅ **Complete Automation**: No more manual intervention required  
✅ **Real-time Tracking**: Live progress updates for all operations  
✅ **Scalable Architecture**: Ready for multi-user environments  
✅ **Docker Native**: Consistent deployment across environments  
✅ **Frontend Integration**: Seamless PHP ↔ Python communication  
✅ **Error Recovery**: Robust error handling and retry mechanisms  
✅ **Data Persistence**: Never lose your jobs or resumes  
✅ **Background Processing**: Non-blocking operations  

The system now operates as a complete, production-ready application with AI automation at its core.